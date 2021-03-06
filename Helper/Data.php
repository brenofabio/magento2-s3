<?php
namespace Thai\S3\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Thai\S3\Model\MediaStorage\File\Storage;

/**
 * Helper for config data.
 */
class Data extends AbstractHelper
{
    /**
     * @var bool
     */
    private $useS3;

    public function __construct(
        EncryptorInterface $encryptor,
        \Magento\Framework\App\Helper\Context $context

    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;

    }

    /**
     * Check whether we are allowed to use S3 as our file storage backend.
     *
     * @return bool
     */
    public function checkS3Usage()
    {
        if (null === $this->useS3) {
            $currentStorage = (int)$this->scopeConfig->getValue(Storage::XML_PATH_STORAGE_MEDIA);
            $this->useS3 = $currentStorage === Storage::STORAGE_MEDIA_S3;
        }

        return $this->useS3;
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        $accessKey =  $this->scopeConfig->getValue('thai_s3/general/access_key');
        return $this->encryptor->decrypt($accessKey);
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        $secretKey = $this->scopeConfig->getValue('thai_s3/general/secret_key');
        return $this->encryptor->decrypt($secretKey);
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->scopeConfig->getValue('thai_s3/general/region');
    }

    /**
     * @return string
     */
    public function getEndpointEnabled()
    {
        return $this->scopeConfig->getValue('thai_s3/custom_endpoint/enabled');
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->scopeConfig->getValue('thai_s3/custom_endpoint/endpoint');
    }

    /**
     * @return string
     */
    public function getEndpointRegion()
    {
        return $this->scopeConfig->getValue('thai_s3/custom_endpoint/region');
    }

    /**
     * @return string
     */
    public function getBucket()
    {
        return $this->scopeConfig->getValue('thai_s3/general/bucket');
    }

    public function getExpires()
    {
        return $this->scopeConfig->getValue('thai_s3/headers/expires');
    }

    /**
     * @return string
     */
    public function getCacheControl()
    {
        return $this->scopeConfig->getValue('thai_s3/headers/cache_control');
    }

    /**
     * @return string
     */
    public function getCustomHeaders()
    {
        return $this->scopeConfig->getValue('thai_s3/headers/custom_headers');
    }
}
