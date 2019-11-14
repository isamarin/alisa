<?php

namespace isamarin\Alisa;

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
    private static $session_id;
    private static $client_id;
    private static $message_id;
    private static $type;
    private static $user_id;
    private static $newSession;
    private static $payload;
    private static $arWords;
    private static $badLanguage;
    private static $utterance;

    final public function __construct($data = null)
    {
        if ( ! $data) {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        self::$session_id = $data[self::SESSION]['session_id'];
        self::$user_id = $data[self::SESSION]['user_id'];
        self::$client_id = $data['meta']['client_id'];
        self::$message_id = (int)$data[self::SESSION]['message_id'];
        self::$type = $data[self::REQUEST]['type'];
        self::$newSession = (bool)$data[self::SESSION]['new'];
        self::$arWords = $data[self::REQUEST]['nlu']['tokens'];

        if (isset($data[self::REQUEST]['original_utterance'])){
            self::$utterance = $data[self::REQUEST]['original_utterance']?:'';
        }

        if (isset($data[self::REQUEST]['markup']) && $markup = $data[self::REQUEST]['markup']['dangerous_context']) {
            self::$badLanguage = (bool)$markup;
        } else {
            self::$badLanguage = false;
        }

        if (isset($data[self::REQUEST]['payload'])) {
            self::$payload = $data[self::REQUEST]['payload'];
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
        return self::$client_id;
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
        return self::$user_id;
    }

    /**
     * Получить идентификатор сессии
     *
     * Уникальный идентификатор сессии, максимум 64 символов.
     * @return string
     */
    final public function getSessionID(): string
    {
        return self::$session_id;
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
        return self::$message_id;
    }

    /**
     * Проверка на тип ввода информации с помощью кнопки
     * @return bool
     */
    final public function isButtonClick(): bool
    {
        return 'ButtonPressed' === self::$type;
    }

    /**
     * Проверка на тип ввода информации с помощью голоса или текста
     * @return bool
     */
    final public function isVoiceRequest(): bool
    {
        return 'SimpleUtterance' === self::$type;
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
     * @see Button::linkTrigger()
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
                'message_id' => self::$message_id,
                'session_id' => self::$session_id,
                'user_id' => self::$user_id,
            ],
            'version' => self::VERSION,
        ];
    }

    /**
     * Полный текст пользовательского запроса, максимум 1024 символа.
     * @return string
     * @see Request::getWords()
     */
    final public function getUtterance(): string
    {
        return self::$utterance?:' ';
    }

}