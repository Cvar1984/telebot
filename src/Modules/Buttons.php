<?php

namespace Cvar1984\TeleBot\Modules;
use Zelenin\Telegram\Bot\Type;

class Buttons {

    protected array $buttonNames;
    protected array $buttonCallbacks;
    protected array $buttons;

    public function __construct()
    {
        return $this;
    }
    public function make()
    {
        $buttonCount = count($this->buttonNames);
        for($x = 0; $x < $buttonCount; $x++) {
            $this->buttons[] = [
                new Type\InlineKeyboardButton([
                    'text' => $this->buttonNames[$x],
                    'callback_data' => $this->buttonCallbacks[$x],
                ]),
            ];
        }

        return new Type\ReplyKeyboardMarkup([
            'keyboard' => $this->buttons
        ]);

    }
    public function addButton(string $buttonName, string $buttonCallback)
    {
        $this->buttonNames[] = $buttonName;
        $this->buttonCallbacks[] = $buttonCallback;
        return $this;
    }
}
