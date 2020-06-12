<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// APIのLINEBOTクラスをインスタンス
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineBotController extends Controller
{
    public function index() {
        return view('linebot.index');
    }
    public function parrot(Request $request)
    {
        // Log::debug(出力したい内容)で、ログファイルに情報を出力できます。

        Log::debug($request->header());
        Log::debug($request->input());

        // トークンの値を返す
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        // 署名の検証
        $signature = $request->header('x-line-signature');
        if (!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid signature');
        }

        $events = $lineBot->parseEventRequest($request->getContent(), $signature);
        Log::debug($events);

        // 複数存在する可能性のあるイベントを繰り返し処理
        foreach ($events as $event) {
            // $event instanceof TextMessage で$eventがTextMessageのインスタンスかどうかを判断
            if (!($event instanceof TextMessage)) {
                // TextMessageでなければログファイルを出力
                Log::debug('Non text message has come');
                continue;
            }
            // 応答トークンReplyTokenを取り出す
            $replyToken = $event->getReplyToken();

            // getText で送られてきたテキスト情報を取得
            $replyText = $event->getText();

            if($replyText === 'おはよう'){

                $lineBot->replyText($replyToken, '今日も一日頑張りましょう！');
            } else {

                // replyTextにgetTextを引数として渡し、そのまま返信する
                $lineBot->replyText($replyToken, $replyText);
            }

        }
    }
}
