<?php

namespace Tests\Unit;

use App\Rules\PublicUrlRule;
use App\Rules\UrlSchemeRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UrlTest extends TestCase
{
    public function testUrls()
    {
        $tests = [
            'www.google.com' => false,
            'http://www.google.com' => true,
            'https://www.google.com' => true,
            '127.0.0.1' => false,
            'localhost' => false,
            'http://localhost' => false,
            'http://cloud.opendialog.dev' => false,
            'http://10.0.0.2' => false,
            'http://192.168.0.1' => false,
            'http://216.0x3a.0000326.0xe3' => false,
            'https://lisaqna.azurewebsites.net/qnamaker/knowledgebases/xx/generateAnswer' => true,
        ];

        foreach ($tests as $url => $shouldPass) {
            $this->urlRuleTester($url, $shouldPass);
        }
    }

    private function urlRuleTester($url, $shouldPass)
    {
        $validator = Validator::make(['url' => $url], [
            'url' => ['active_url', new PublicUrlRule, new UrlSchemeRule]
        ]);

        $passes = $validator->passes();

        if ($shouldPass) {
            $errorMessages = $validator->errors()->getMessages() ? implode(",", $validator->errors()->getMessages()['url']) : "";
            $this->assertTrue(
                $passes,
                sprintf(
                    'URL %s should have passed, but failed with these messages %s',
                    $url,
                    $errorMessages
                )
            );
        } else {
            $this->assertFalse($passes, sprintf('URL %s should have failed, but passed', $url));
        }
    }
}
