<?php

namespace Tests\Unit;

use App\Http\Controllers\PublishingController;
use Illuminate\Http\Testing\File;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class PublishingCoverValidationTest extends TestCase
{
    public function test_cover_at_five_megabytes_is_accepted()
    {
        $validator = $this->makeCoverValidator(5000000);

        $this->assertFalse($validator->fails());
    }

    public function test_cover_over_five_megabytes_is_rejected()
    {
        $validator = $this->makeCoverValidator(5000001);

        $this->assertTrue($validator->fails());
    }

    private function makeCoverValidator($sizeInBytes)
    {
        $file = File::image('cover.png');
        $file->sizeToReport = $sizeInBytes;
        $request = Request::create('/', 'POST', [], [], ['cover' => $file]);

        $validator = $this->app['validator']->make(
            ['cover' => $file],
            [
                'cover' => 'nullable|image|mimes:jpg,jpeg,png',
            ]
        );

        $method = new ReflectionMethod(PublishingController::class, 'addCoverSizeLimit');
        $method->setAccessible(true);
        $method->invoke(new PublishingController(), $validator, $request);

        return $validator;
    }
}
