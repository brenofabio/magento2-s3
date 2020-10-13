<?php
namespace Thai\S3\Model\MediaStorage\File\Storage\Synchronisation;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Thai\S3\Model\MediaStorage\File\Storage\S3Factory;

/**
 * Plugin for Synchronization.
 *
 * @see Synchronization
 */
class Plugin
{
    /**
     * S3 storage factory
     *
     * @var DatabaseFactory
     */
    private $storageFactory;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $mediaDirectory;

    /**
     * @param S3Factory $storageFactory
     * @param \Magento\Framework\Filesystem $filesystem,

     */
    public function __construct(
        S3Factory $storageFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->storageFactory = $storageFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param Synchronization $subject
     * @param string $relativeFileName
     * @throws \LogicException
     * @return string
     */
    public function beforeSynchronize($subject, $relativeFileName)
    {
        $normalisedRelativeFileName = $this->getNormalisedRelativeFileName($relativeFileName);

        /** @var $storage Database */
        $storage = $this->storageFactory->create();

        try {
            $storage->loadByFilename($normalisedRelativeFileName);
        } catch (\Exception $e) {
        }

        if ($storage->getId()) {
            /** @var WriteInterface $file */
            $file = $this->mediaDirectory->openFile($normalisedRelativeFileName, 'w');
            try {
                $file->lock();
                $file->write($storage->getContent());
                $file->unlock();
                $file->close();
            } catch (FileSystemException $e) {
                $file->close();
            }
        }

        return $relativeFileName;
    }

    /**
     * The value of the relativeFileName param in Magento 2.3 is prefixed with
     * "media/" whereas older versions don't include this prefix.
     *
     * This plugin strips out the "media/" prefix so we can properly retrieve
     * the object from S3.
     *
     * @param string $relativeFileName
     * @return string
     */
    private function getNormalisedRelativeFileName($relativeFileName)
    {
        $normalisedRelativeFileName = ltrim($relativeFileName, '/');
        $normalisedRelativeFileName = ltrim($normalisedRelativeFileName, 'media');
        return ltrim($normalisedRelativeFileName, '/');
    }
}
