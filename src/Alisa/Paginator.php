<?php

namespace isamarin\Alisa;

use function array_key_exists;
use function count;

class Paginator
{
    protected $links;
    protected $buttons;
    protected $payload;

    protected $limit = 3;

    protected $topage;
    protected $current;
    protected $prevTrigger;

    //TODO
    //[services][back]
    //[services][current]
    /**
     * Paginator constructor.
     * @param Button ...$buttons
     * TODO
     * how to match?
     */
    public function __construct($requestPayload, $prevTrigger)
    {
        $this->payload = $requestPayload;
        $this->prevTrigger = $prevTrigger;
        writeLog($this->payload);
        if (array_key_exists('services', $this->payload)) {
            $this->current = $this->payload['services']['current'];
            $this->topage = $this->payload['services']['topage'];
        }
//        foreach ($buttons as $button) {
//            if (array_key_exists('link', $button->get())) {
//                $this->links[] = $button;
//            } else {
//                $this->buttons[] = $button;
//            }
//        }
    }

    public function append(Button $button)
    {
        if (array_key_exists('link', $button->get())) {
            $this->links[] = $button;
        } else {
            $this->buttons[] = $button;
        }
    }

    /**
     * TODO
     * add service
     */
    public function getPaginated()
    {
        $this->addServiceButtons();
        return array_merge($this->links, $this->buttons);
    }

    protected function addServiceButtons()
    {
        if (count($this->links) > $this->limit) {

            if ( ! isset($this->topage, $this->current)) {
                $this->topage = 2;
                $this->current = 1;
            }
            writeLog('PREV: ' . $this->prevTrigger->getName());

            $this->links = array_slice($this->links, ($this->current - 1) * $this->limit, $this->limit);
            $back = new Button();
            $back->setTitle('Назад');
            $back->linkTrigger($this->prevTrigger);
            $back->setHide(false);
            $back->addPayload([
                'topage' => $this->topage - 1,
                'current' => $this->topage,
            ]);

            if ($this->current !== 1) {
                $this->links[] = $back;
            }

            /**
             * TODO
             * count MORE
             */

            /**
             * TODO
             * hide more
             */
            $more = new Button();
            $more->setTitle('Еще');
            $more->linkTrigger($this->prevTrigger);
            $more->setHide(false);
            $more->addPayload([
                'topage' => $this->topage + 1,
                'current' => $this->topage,
            ]);
            $this->links[] = $more;
        }
    }
}