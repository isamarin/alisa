<?php

namespace isamarin\Alisa;
use function array_slice;
use function count;

/**
 * Class Paginator
 * @package isamarin\Alisa
 */
class Paginator
{
    protected $links = [];
    protected $buttons = [];
    protected $payload;
    protected $limit;
    protected $topage;
    protected $prevTrigger;
    protected $keepData;

    public const TOPAGE = 'topage';
    public const KEEPDATA = 'keepdata';
    public const REPEAT = 'repeat';


    /**
     * Paginator constructor.
     * @param $requestPayload
     * @param $prevTrigger
     * @param $keepPreviosData
     */
    public function __construct($requestPayload, $prevTrigger, $keepPreviosData)
    {
        $this->keepData = $keepPreviosData;
        $this->payload = $requestPayload;
        $this->prevTrigger = $prevTrigger;
        if (isset($this->payload[Button::SERVICES])) {
            $this->topage = $this->payload[Button::SERVICES]['topage'];
        }
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @param Button $button
     */
    public function append(Button $button): void
    {
        if ($button->get()[Button::YANDEX_HIDE] !== false) {
            $this->buttons[] = $button;
        } else {
            $this->links[] = $button;
        }
    }

    /** @internal */
    public function getPaginated(): array
    {
        $this->addServiceButtons();
        if ( ! empty($this->buttons)) {
            return array_merge($this->buttons, $this->links);
        }
        return $this->links;
    }

    protected function addServiceButtons(): void
    {
        $currentCount = count($this->links);

        if ($currentCount > $this->limit) {


            if ( ! isset($this->topage)) {
                $this->topage = 1;
            }

            $this->links = array_slice($this->links, ($this->topage - 1) * $this->limit, $this->limit);
            $less = $currentCount - ($this->topage * $this->limit);
            if ($less !== 0 && $this->topage !== 1) {
                $back = new Button();
                $back->setTitle('← Назад');
                $back->linkTrigger($this->prevTrigger);
                $back->setHide(false);
                $back->addPayload([
                    self::TOPAGE => $this->topage - 1,
                    self::KEEPDATA => $this->keepData,
                    self::REPEAT => true,
                ]);
                $this->links[] = $back;
            }
            if ($less > 0) {
                $more = new Button();
                $more->setTitle("Ещё ($less)");
                $more->linkTrigger($this->prevTrigger);
                $more->setHide(false);
                $more->addPayload([
                    self::TOPAGE => $this->topage + 1,
                    self::KEEPDATA => $this->keepData,
                    self::REPEAT => true,
                ]);
                $this->links[] = $more;
            }
        }
    }
}