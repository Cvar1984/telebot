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
$db = new \PDO($dbDsn);

$botToken = Retriever::getEnv('BOT_TOKEN');
$bot = ApiFactory::create($botToken);
$botService = new Daemon($bot);

$botService
    ->onUpdate(function (UpdateInterface $update) use($bot, $db) {
        print_r($update);
        if(preg_match('/^\/simpan\s(\d+)\s(\w+)/ms', $update->message->text, $result)) {
            $noHp = $result[1];
            $idPaket = $result[2];
            $sql = 'INSERT INTO "penjualan" ("id","nomor_hp","id_packet") VALUES (NULL,:no_hp, :id_packet)';
            
            $sth = $db->prepare($sql, [
                PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
            ]);

            $status = $sth->execute([
                ':no_hp' => $noHp,
                ':id_packet' => $idPaket
            ]);
            
            if($status) {
                $bot->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text'=> sprintf('nomor *%s* dengan paket *%s* tersimpan', $noHp, $idPaket),
                    'parse_mode' => 'Markdown'
                ]);
            } else {
                $bot->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text'=> sprintf('nomor *%s* dengan paket *%s* tidak dapat simpan', $noHp, $idPaket),
                    'parse_mode' => 'Markdown'
                ]);
            }
        }
        elseif(preg_match('/^\/ambil\s(\w+)\s(\d+)\s(\d+)/ms', $update->message->text, $result)) {
            $subject = $result[1];
            $waktuPrimer = $result[2];
            $waktuSekunder = $result[3];
        }
    }
);

$botService->run();
