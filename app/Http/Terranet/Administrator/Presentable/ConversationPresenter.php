<?php

namespace App\Http\Terranet\Administrator\Presentable;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Symfony\Component\Yaml\Yaml;
use Terranet\Presentable\Presenter;

class ConversationPresenter extends Presenter
{
    public function title()
    {
        return link_to_route('scaffold.view', $this->presentable->name, [
            'module' => 'conversations',
            'id' => $this->presentable
        ]);
    }

    public function model()
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['yaml']));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['yaml']));

        $commonMarkConverter = new CommonMarkConverter([], $environment);

        return $commonMarkConverter->convertToHtml("```\n" . $this->presentable->model . "\n```");
    }

    public function outgoingIntents()
    {
        $output = '';

        $yaml = Yaml::parse($this->presentable->model)['conversation'];

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                foreach ($intent as $tag => $value) {
                    if ($tag == 'b') {
                        foreach ($value as $key => $intent) {
                            if ($key == 'i') {
                                $outgoingIntent = OutgoingIntent::where('name', $intent)->first();

                                if ($outgoingIntent) {
                                    $output .= '<div><a href="/admin/outgoing_intents/' . $outgoingIntent->id . '">' . $intent . '</a></div>';
                                } else {
                                    $output .= '<div><a href="/admin/outgoing_intents/new">' . $intent . '</a></div>';
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $output;
    }
}
