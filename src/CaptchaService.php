<?php
/**
 * Created by PhpStorm.
 * User: gopher
 * Date: 12/12/2022
 * Time: 6:46 PM
 */

namespace Share;


class CaptchaService
{
    /** @var App */
    private $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function createImage() {
        try {
            $text = bin2hex(random_bytes(2));
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        $this->app->setSession('user.captcha', $text);
        $image = imagecreatetruecolor(130, 43);
        $black = imagecolorallocate($image, 0, 0, 0);
        //$gray = imagecolorallocate($image, 192, 192, 192);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        for ($i = 0; $i < 6; $i++) {
            try {
                $bgText = bin2hex(random_bytes(16));
            } catch (\Exception $e) {
                throw new \RuntimeException($e->getMessage());
            }
            imagestring($image, 1, 0, $i * 7, bin2hex($bgText), $black);
        }
        for ($i = 0; $i < strlen($text); $i++) {
            imagettftext($image, 30, mt_rand(-20, 20), 5 + $i * 30, 36, -$white, dirname(__FILE__).'/../fonts/arial-black.ttf', $text[$i]);
        }
        return imagepng($image);
    }

}