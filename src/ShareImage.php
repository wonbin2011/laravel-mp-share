<?php

namespace Wonbin\Miniprogram\Share;

use VerumConsilium\Browsershot\Facades\Screenshot;
use Illuminate\Database\Eloquent\Model;
use Storage;

class ShareImage
{

    /**
     * 生成海报.
     *
     * @param $url
     *
     * @return array|bool
     */
    public static function generateShareImage(string $url)
    {
        if (!$url) {
            return false;
        }

        $width  = config('wonbin.mp-share-poster.width', '575');
        $height  = config('wonbin.mp-share-poster.height', '820');


        $saveName           = config('wonbin.mp-share-poster.default.app') . '/' .date('Ymd') . '/'  .  md5(uniqid()) . '.jpeg';
        $storage            = config('wonbin.mp-share-poster.default.storage');
        $storagePath        = config('wonbin.mp-share-poster.disks.' . $storage . '.root');


        $jpegStoredPath = Screenshot::loadUrl($url)
            ->useJPG()
            ->noSandBox()
            ->margins(20, 0, 0, 20)
            ->windowSize($width,$height)
            ->storeAs($storagePath,$saveName);

        if (config('wonbin.mp-share-poster.compress', true)) {
            self::compress($jpegStoredPath);
        }

        if ('qiniu' == $storage) {
            Storage::disk($storage)->put($saveName, file_get_contents($jpegStoredPath));
        }

        return [
            'url'  => Storage::disk($storage)->url($saveName),
            'path' => $saveName,
        ];
    }

    /**
     * 压缩图片.
     *
     * @param $file
     */
    public static function compress($file)
    {
        list($width, $height, $type) = getimagesize($file);
        $new_width  = $width * 1;
        $new_height = $height * 1;

        $resource = imagecreatetruecolor($new_width, $new_height);
        $image    = imagecreatefromjpeg($file);
        imagecopyresampled($resource, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($resource, $file, config('wonbin.mp-share-poster.quality', 9));
        imagedestroy($resource);
    }

    /**
     * 绑定关系.
     *
     * @param Model $model
     * @param array $path
     *
     * @return Poster
     */
    public static function attach(Model $model, array $path)
    {
        $poster = Poster::create(['content' => $path, 'posterable_id' => $model->id, 'posterable_type' => get_class($model)]);

        return $poster;
    }

    /**
     * 关系是否存在.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public static function exists(Model $model)
    {
        $poster = Poster::where('posterable_id', $model->id)->where('posterable_type', get_class($model))->first();
        if ($poster) {
            return $poster;
        }

        return false;
    }

    /**
     * 生成海报.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param                                     $url
     * @param bool                                $rebuild
     *
     * @return array|bool
     */
    public static function run(Model $model, $url, $rebuild = false)
    {
        $path   = [];
        $poster = self::exists($model);
        if ($poster) {
            $path = $poster->content;
        }

        if ($rebuild || !$poster) {
            $path = self::generateShareImage($url);
        }

        if (!$poster) {
            self::attach($model, $path);
        }

        if ($poster && $rebuild) {
            $old     = $poster->content;
            $storage = config('wonbin.mp-share-poster.default.storage');
            if (config('ibrand.miniprogram-poster.delete', true) && !empty($old) && isset($old['path']) && Storage::disk($storage)->exists($old['path'])) {
                Storage::disk($storage)->delete($old['path']);
            }
            $poster->content = $path;
            $poster->save();
        }

        return $path;
    }
}