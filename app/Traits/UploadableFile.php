<?php

namespace App\Traits;

// use Str;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait UploadableFile
{
    /**
     * Returns one file path
     * @param $file
     * @param $folder
     * @param $disk
     * @param $filename
     *
     * @return mixed
     */
    public function uploadOne($file, $folder = null, $disk = 'public', $filename = null): mixed
    {
        $filename =  Str::random() . '.' . $filename;
        return $file->store($folder, [
            'disk' => $disk,
            'filename' => $filename,
        ]);
    }


    /**
     * Returns multiple file paths
     * @param $files
     * @param $folder
     * @param $disk
     *
     * @return array
     */
    public function uploadMany($files, $folder = null, $disk = 'public'): array
    {
        foreach ($files as $file) {
            $filename =  Str::random() . '.' . $file->getClientOriginalName();
            $file->storeAs($folder, $filename, [
                'disk' => $disk,
            ]);
            $data[] = $filename;
        }

        return $data;
    }

    public function saveImage($base64, $defaultPathFolder)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($base64, strpos($base64, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $fileName = Str::random() . '.' . $type;
        $filePath = $defaultPathFolder . '/' . $fileName;

        // Guardar el archivo en AWS S3
        Storage::disk('s3')->put($filePath, $image);

        return $filePath;
    }

    public function getImageAsBase64($imageUrl)
    {
        // Obtener el contenido de la imagen de la URL en base64
        $imageContent = file_get_contents($imageUrl);
        $imageBase64 = base64_encode($imageContent);

        // Obtener el tipo de la imagen (por ejemplo, 'png')
        $imageType = pathinfo($imageUrl, PATHINFO_EXTENSION);

        // Construir el prefijo del formato de imagen
        $imagePrefix = 'data:image/' . $imageType . ';base64,';

        // Devolver la imagen en formato base64 con el prefijo
        return $imagePrefix . $imageBase64;
    }
}
