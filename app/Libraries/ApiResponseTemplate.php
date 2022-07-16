<?php

namespace App\Libraries;

/**
 * 공통 API Response
 */
class ApiResponseTemplate
{
    /**
     * 상태코드
     * @var int
     */
    private int $statusCode = 200;

    /**
     * 결과
     * @var array|null
     */
    private ?array $result = null;

    /**
     * description
     * @var string
     */
    private string $desc = '';

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array $result
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc(string $desc): void
    {
        $this->desc = $desc;
    }

    /**
     * ApiResponseTemplate Object -> Array Convert
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->statusCode,
            'result' => $this->result,
            'desc' => $this->desc
        ];
    }
}