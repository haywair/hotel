<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2016/11/10
 */

namespace app\common\base;

Class Error
{
    private $code;
    private $text;
    private $data;

    public function __construct($errordata = array())
    {

        if (!empty($errordata['code'])) {
            $this->setCode($errordata['code']);
        }

        if (!empty($errordata['text'])) {
            $this->setText($errordata['text']);
        }

        if (!empty($errordata['data'])) {
            $this->setData($errordata['data']);
        }
    }

    public function setError($code, $text, $data = array())
    {
        $this->setCode($code);
        $this->setText($text);
        $this->setData($data);
    }

    public function setOk($data = array(), $text = "")
    {
        $this->setCode(0);
        $this->setText($text);
        $this->setData($data);
    }

    public function getResult()
    {
        $e = array();
        $e['result'] = $this->checkResult();
        $e['code'] = $this->getCode();
        $e['text'] = $this->getText();
        $e['data'] = $this->getData();
        return $e;
    }

    public function checkResult()
    {
        if ($this->getCode() > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function returnJson()
    {
        return (['code' => $this->getCode(), 'text' => $this->getText(), 'data' => $this->getData()]);
    }
}