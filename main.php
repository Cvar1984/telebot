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
            $sql = 'INSERT INTO "penjualan" ("id","no_hp","id_packet") VALUES (NULL,:no_hp, :id_packet)';
            
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
        elseif(preg_match('/^\/ambil\s(\w+)\s(\d{2})-(\d{2})\s(\d{2})-(\d{2})/ms', $update->message->text, $result)) {
            $subject = $result[1];
            $hariPrimer = $result[2];
            $bulanPrimer = $result[3];
            $hariSekunder = $result[4];
            $bulanSekunder = $result[5];

            $tahun = date('Y');
            $tanggalPrimer = sprintf('%s-%s-%s', $tahun, $bulanPrimer, $hariPrimer);
            $tanggalSekunder = sprintf('%s-%s-%s', $tahun, $bulanSekunder, $hariSekunder);
            $sql = 'SELECT * from "penjualan" WHERE "waktu_simpan" BETWEEN :waktu_primer AND :waktu_sekunder';

            $sth = $db->prepare($sql, [
                PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
            ]);
            

            $sth->execute([
                ':waktu_primer' => $tanggalPrimer,
                ':waktu_sekunder' => $tanggalSekunder,
            ]);

            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => json_encode($result, JSON_PRETTY_PRINT),
            ]);
        }
    }
);

$botService->run();
