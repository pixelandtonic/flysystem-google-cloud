<?php

namespace League\Flysystem\GoogleCloud;

use Aws\Common\Exception\MultipartUploadException;
use Aws\S3\Enum\Group;
use Aws\S3\Enum\Permission;
use Aws\S3\Model\MultipartUpload\AbstractTransfer;
use Aws\S3\Model\MultipartUpload\UploadBuilder;
use Aws\S3\S3Client;
use Guzzle\Service\Resource\Model;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;

class GoogleCloudAdapter extends AwsS3Adapter
{
    /**
     * {@inheritdoc}
     */
    public function deleteDir($path)
    {
        $path = rtrim($this->applyPathPrefix($path), '/').'/';

        $success = true;

        $objects = $this->listContents($path);
        $directoryList = [];

        foreach ($objects as $object)
        {
            if ($object['type'] == 'dir')
            {
                $directoryList[] = $object['path'];
            }
            else
            {
                $success = $success && $this->delete($object['path']);
            }
        }

        foreach ($directoryList as $directoryPath)
        {
            $directoryPath = rtrim($this->applyPathPrefix($directoryPath), '/').'/';

            // This operation can return false as well, if the directory was not
            // an object itself but only part of the path for the files.
            $this->delete($directoryPath);
        }

        return $success;
    }
}
