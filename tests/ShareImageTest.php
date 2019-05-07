<?php

namespace Wonbin\Miniprogram\Share\Test;

use Wonbin\Miniprogram\Share\ShareImage;
use Wonbin\Miniprogram\Share\Poster;
use Storage;

class ShareImageTest extends BaseTest
{
    /** @test */
    public function TestConfig()
    {
        $config = config('filesystems.disks');

        $this->assertArrayHasKey('MpShare', $config);

        $this->assertArrayHasKey('qiniu', $config);
    }

    /** @test */
    public function TestGenerateShareImage()
    {
        config(['wonbin.mp-share-poster.height' => '820']);

        $url    = 'https://www.ibrand.cc/';
        $result = ShareImage::generateShareImage($url);
        $this->assertTrue(Storage::disk('MpShare')->exists($result['path']));

        $result = ShareImage::generateShareImage('');
        $this->assertFalse($result);
    }

    /** @test */
    public function TestShareImageV2()
    {
        config(['ibrand.miniprogram-poster.width' => '1300']);

        $url   = 'https://www.ibrand.cc/';
        $goods = GoodsTestModel::find(1);

        //1. first build.
        $result  = ShareImage::run($goods, $url);
        $oldPath = $result['path'];
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertEquals(1, count($goods->posters));

        //2. rebuild and delete old.
        $result   = ShareImage::run($goods, $url, true);
        $oldPath2 = $result['path'];
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertFalse(Storage::disk('MiniProgramShare')->exists($oldPath));
        $this->assertEquals(1, count($goods->posters));

        //3. rebuild but not delete old.
        $this->app['config']->set('ibrand.miniprogram-poster.delete', false);
        $result = ShareImage::run($goods, $url, true);
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($oldPath2));

        $poster = Poster::find(1);
        $this->assertEquals(GoodsTestModel::class, get_class($poster->posterable));
    }

    /** @test */
    public function TestSaveToQiNiu()
    {
        config(['ibrand.miniprogram-poster.default.storage' => 'qiniu']);

        $config = config('ibrand.miniprogram-poster');
        $this->assertSame($config['default']['storage'], 'qiniu');

        $url    = 'https://www.ibrand.cc/';
        $result = ShareImage::generateShareImage($url);
        $this->assertTrue(Storage::disk('qiniu')->exists($result['path']));

        $goods  = GoodsTestModel::find(1);
        $result = ShareImage::run($goods, $url);
        $this->assertTrue(Storage::disk('qiniu')->exists($result['path']));
        $this->assertEquals(1, count($goods->posters));
    }
}