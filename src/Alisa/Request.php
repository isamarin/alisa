<?php

namespace isamarin\Alisa;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Запрос от Яндекс.Алиса
 *
 * Полученная реплика пользователя от Алисы.
 * @package Alisa
 * @author Igor Samarin <mako.mmw@gmail.com>
 * @see https://tech.yandex.ru/dialogs/alice/doc/protocol-docpage/
 */
class Request implements Interfaces\RequestInterface
{
    protected const SESSION = 'session';
    protected const REQUEST = 'request';
    protected const VERSION = '1.0';
    public const SUBSTITUTED_TO = 'to';
    public const SUBSTITUTED_FROM = 'from';
    public const SUBSTITUTED = 'substituted';


    private static $sessionID;
    private static $clientID;
    private static $messageID;
    private static $type;
    private static $userID;
    private static $newSession;
    private static $payload;
    private static $arWords;
    private static $badLanguage;
    private static $utterance;
    private static $substitute = false;
    private $rawRequest;

    /**
     * Request constructor.
     * @param null $data
     */
    final public function __construct($data = null)
    {
        if ( ! $data) {
            $this->rawRequest = json_decode(file_get_contents('php://input'), true);
        }


        self::$sessionID = $this->rawRequest[self::SESSION]['session_id'];
        self::$userID = $this->rawRequest[self::SESSION]['user_id'];
        self::$clientID = $this->rawRequest['meta']['client_id'];
        self::$messageID = (int)$this->rawRequest[self::SESSION]['message_id'];
        self::$type = $this->rawRequest[self::REQUEST]['type'];
        self::$newSession = (bool)$this->rawRequest[self::SESSION]['new'];
        self::$arWords = $this->rawRequest[self::REQUEST]['nlu']['tokens'];

        if (isset($this->rawRequest['substituted'])) {
            self::$substitute = $this->rawRequest['substituted'];
        }

        if (isset($this->rawRequest[self::REQUEST]['original_utterance'])) {
            self::$utterance = $this->rawRequest[self::REQUEST]['original_utterance'] ?: '';
        }

        if (isset($this->rawRequest[self::REQUEST]['markup']) && $markup = $this->rawRequest[self::REQUEST]['markup']['dangerous_context']) {
            self::$badLanguage = (bool)$markup;
        } else {
            self::$badLanguage = false;
        }

        if (isset($this->rawRequest[self::REQUEST]['payload'])) {
            self::$payload = $this->rawRequest[self::REQUEST]['payload'];
        }
    }

    /**
     * Получить идентификатор клиента
     *
     * Идентификатор устройства и приложения, в котором идет разговор, максимум 1024 символа.
     * @return string
     */
    final public function getClientID(): string
    {
        return self::$clientID;
    }

    /**
     * Получить идентификатор экземпляра приложения
     *
     * Даже если пользователь авторизован с одним и тем же аккаунтом в приложении Яндекс для Android
     * и iOS, Яндекс.Диалоги присвоят отдельный user_id каждому из этих приложений.
     * @return string
     */
    final public function getUserID(): string
    {
        return self::$userID;
    }

    /**
     * Получить идентификатор сессии
     *
     * Уникальный идентификатор сессии, максимум 64 символов.
     * @return string
     */
    final public function getSessionID(): string
    {
        return self::$sessionID;
    }

    /**
     * Получить идентификатор сообщения
     *
     * Идентификатор сообщения в рамках сессии, максимум 8 символов.
     * Инкрементируется с каждым следующим запросом.
     * @return int
     */

    final public function getMessageID(): int
    {
        return self::$messageID;
    }

    /**
     * Проверка на тип ввода информации с помощью кнопки
     * @return bool
     */
    final public function isButtonClick(): bool
    {
        return self::$type === 'ButtonPressed';
    }

    /**
     * Проверка на тип ввода информации с помощью голоса или текста
     * @return bool
     */
    final public function isVoiceRequest(): bool
    {
        return self::$type === 'SimpleUtterance';
    }

    /**
     * Признак новой сессии
     * @return bool
     */
    final public function isNewSession(): bool
    {
        return self::$newSession;
    }

    /**
     * Возвращает информацию, которая была передана с нажатой кнопки
     * @return mixed
     * @see Button::delegateTo()
     */
    final public function getPayloadData()
    {
        return self::$payload;
    }

    /**
     * Получить массив слов, которые были введены пользователем
     * @return array
     * @see Request::getUtterance()
     */
    final public function getWords(): array
    {
        return self::$arWords;
    }

    /**
     * Признак реплики, которая содержит криминальный подтекст (самоубийство, разжигание ненависти, угрозы).
     * Вы можете настроить навык на определенную реакцию для таких случаев — например, отвечать «Не понимаю, о чем вы. Пожалуйста, переформулируйте вопрос.»
     * @return bool
     */
    final public function isBadLanguage(): bool
    {
        return self::$badLanguage;
    }

    /**
     * Возвращает сервисную информацию, необходимую для отправки в теле ответа
     * @return array
     */
    final public function getServiceData(): array
    {
        return [
            'session' => [
                'message_id' => self::$messageID,
                'session_id' => self::$sessionID,
                'user_id' => self::$userID,
            ],
            'version' => self::VERSION,
        ];
    }

    /**
     * @return array
     */
    final public function getRAW(): array
    {
        return $this->rawRequest;
    }

    /**
     * Полный текст пользовательского запроса, максимум 1024 символа.
     * @return string
     * @see Request::getWords()
     */
    final public function getUtterance(): string
    {
        return self::$utterance ?: ' ';
    }

    /**
     * Это перенаправленный триггером запрос?
     */
    final public function isSubstitued()
    {
        return self::$substitute;
    }

    /**
     * Перенаправить запрос на самого себя с целью передачи делегации другому триггеру
     */
    /**
     * @param $fromTriggerName
     * @param $toTriggerName
     */
    final public function makeSubstitued($fromTriggerName, $toTriggerName): void
    {
        $this->rawRequest[self::SUBSTITUTED][self::SUBSTITUTED_FROM] = $fromTriggerName;
        $this->rawRequest[self::SUBSTITUTED][self::SUBSTITUTED_TO] = $toTriggerName;
        $gClient = new Client();
        $response = $gClient->post('https://' . $_SERVER['HTTP_HOST'], [
            RequestOptions::JSON => $this->rawRequest,
        ]);
        die($response->getBody()->getContents());
    }

}