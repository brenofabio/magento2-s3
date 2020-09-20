<?php
namespace Thai\S3\Service\MediaStorage\ImageResize;

use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Service\ImageResize;

class Plugin
{
    /**
     * @var MediaConfig
     */
    protected $imageConfig;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var FileStorageDatabase
     */
    protected $fileStorageDatabase;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param MediaConfig $imageConfig
     * @param Filesystem $filesystem
     * @param Database $fileStorageDatabase
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        MediaConfig $imageConfig,
        Filesystem $filesystem,
        Database $fileStorageDatabase = null,
        LoggerInterface $logger
    ) {
        $this->imageConfig = $imageConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->logger = $logger;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            ObjectManager::getInstance()->get(Database::class);
    }

    /**
     * Create resized images of different sizes from an original image.
     *
     * @param string $originalImageName
     * @throws NotFoundException
     */
    public function aroundResizeFromImageName($subject, \Closure $proceed, $originalImageName)
    {
        $mediastoragefilename = $this->imageConfig->getMediaPath($originalImageName);
        $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);

        $this->logger->debug('Debug', [
            $originalImageName,
            $mediastoragefilename,
            $originalImagePath
        ]);

        if ($this->fileStorageDatabase->checkDbUsage() &&
            !$this->mediaDirectory->isFile($mediastoragefilename)
        ) {
            $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
        }

        if (!$this->mediaDirectory->isFile($originalImagePath)) {
            throw new NotFoundException(__('Cannot resize image "%1" - original image not found', $originalImagePath));
        }

        foreach ($this->getViewImages($this->getThemesInUse()) as $viewImage) {
            $this->resize($viewImage, $originalImagePath, $originalImageName);
        }

        return $proceed($originalImageName);
    }
}
