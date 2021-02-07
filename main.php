<?php

require './vendor/autoload.php';

use Cvar1984\TeleBot\Modules;
use Ahc\Env\Loader;
use Ahc\Env\Retriever;
use Zelenin\Telegram\Bot\ApiFactory;
use Zelenin\Telegram\Bot\Daemon\NaiveDaemon as Daemon;
use Zelenin\Telegram\Bot\Type\Update as UpdateInterface;
use Zelenin\Telegram\Bot\Type;

// Load to all targets:
(new Loader)->load('./.env', true, Loader::ALL);

$dbDriver = Retriever::getEnv('DATA_BASE_DRIVER');
$dbName = Retriever::getEnv('DATA_BASE_NAME');
$dbDsn = sprintf('%s:./assets/%s.sqlite', $dbDriver, $dbName);
$db = Modules\Singleton::make(new \PDO($dbDsn));

$botToken = Retriever::getEnv('BOT_TOKEN');
$botApi = ApiFactory::create($botToken);
$botService = new Daemon($botApi);

$botService
    ->onUpdate(function (UpdateInterface $update) use($botApi) {
        print_r($update);
        $buttons[] = [
            new Type\InlineKeyboardButton([
                'text' => '/masukan_data',
                'callback_data' => 'ok',
            ]),
        ];
        $buttons[] = [
            new Type\InlineKeyboardButton([
                'text' => '/masukan_data2',
                'callback_data' => 'ok',
            ]),
        ];


        $response = $botApi->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => 'Pilih menu yg tersedia',
            'reply_markup' => new Type\ReplyKeyboardMarkup([
                'keyboard' => $buttons
            ])
        ]);

        $botApi->sendMessage([
            'chat_id' => $update->message->chat->id,
            'text' => sprintf('anda memilih %s', $update->message->text),
            'reply_markup' => new Type\ReplyKeyboardHide([
                'hide_keyboard' => true
            ])
        ]);
    }
);

$botService->run();
