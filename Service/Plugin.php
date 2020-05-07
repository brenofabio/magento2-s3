<?php 
namespace Thai\S3\Service;

use Magento\Framework\Filesystem;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

class Plugin
{
    protected $imageConfig;

    protected $mediaDirectory;

    protected $fileStorageDatabase;

    public function __construct(
        MediaConfig $imageConfig,
        Filesystem $filesystem,
        Database $fileStorageDatabase = null
    ) {
        $this->imageConfig = $imageConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            ObjectManager::getInstance()->get(Database::class);
    }
    /**
     * 
     */
    public function aroundResizeFromImageName(\Magento\MediaStorage\Service\ImageResize $subject, \Closure $proceed, $originalImageName)
    {
        try{
            $mediastoragefilename = $this->imageConfig->getMediaPath($originalImageName);
            $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->mediaDirectory->isFile($mediastoragefilename)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
            }
            return $proceed($originalImageName);
        }catch(\Exception $e){
            //echo $e->getMessage();
        }
        
    }
}