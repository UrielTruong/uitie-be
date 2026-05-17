<?php

namespace App\Services;

use App\Models\Attachment;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    private string $disk = 'minio';

    public function generatePresignedUrl(string $fileName, string $mimeType, string $folder =
    'posts'): array
    {
        try {
            $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
            $key      = "{$folder}/" . Str::uuid() . ".{$ext}";
            $fileType = $this->resolveFileType($mimeType);

            $s3Client = new S3Client([
                'version'                 => 'latest',
                'region'                  => config('filesystems.disks.minio.region'),
                'endpoint'                => config('filesystems.disks.minio.endpoint'),
                'use_path_style_endpoint' =>
                config('filesystems.disks.minio.use_path_style_endpoint', true),
                'credentials'             => [
                    'key'    => config('filesystems.disks.minio.key'),
                    'secret' => config('filesystems.disks.minio.secret'),
                ],
            ]);

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket'      => config('filesystems.disks.minio.bucket'),
                'Key'         => $key,
                'ContentType' => $mimeType,
            ]);

            $presignedUrl = (string) $s3Client->createPresignedRequest($cmd, new \DateTime('+10 minutes'))->getUri();
            $fileUrl      = rtrim(config('filesystems.disks.minio.endpoint'), '/')
                . '/' . config('filesystems.disks.minio.bucket')
                . '/' . $key;
        } catch (\Throwable $e) {
            return ["error", $e];
        }

        Cache::put("fname:{$fileUrl}", $fileName, now()->addHour());

        return [
            'presigned_url' => $presignedUrl,
            'file_url'      => $fileUrl,
            'file_type'     => $fileType,
            'file_name'     => $fileName,
        ];
    }

    private function resolveFileType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return Attachment::TYPE_IMAGE;
        if (str_starts_with($mime, 'video/')) return Attachment::TYPE_VIDEO;
        return Attachment::TYPE_DOCUMENT;
    }

    public function getViewUrl(string $fileUrl, int $minutes = 60): string
    {
        $bucket = config('filesystems.disks.minio.bucket');
        $endpoint = rtrim(config('filesystems.disks.minio.endpoint'), '/');
        $key = Str::after($fileUrl, $endpoint . '/' . $bucket . '/');

        $s3Client = new S3Client([
            'version'                 => 'latest',
            'region'                  => config('filesystems.disks.minio.region'),
            'endpoint'                => $endpoint,
            'use_path_style_endpoint' => config('filesystems.disks.minio.use_path_style_endpoint', true),
            'credentials'             => [
                'key'    => config('filesystems.disks.minio.key'),
                'secret' => config('filesystems.disks.minio.secret'),
            ],
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $key,
        ]);

        return (string) $s3Client->createPresignedRequest($cmd, new \DateTime("+{$minutes} minutes"))->getUri();
    }
}
