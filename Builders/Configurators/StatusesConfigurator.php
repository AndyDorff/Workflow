<?php

namespace Modules\Workflow\Builders\Configurators;

final class StatusesConfigurator
{
    private $statuses;
    private $initialStatus;
    private $metadata = [];

    public function __construct(array $statuses)
    {
        $this->statuses = $statuses;
        $this->initialStatus = current($statuses) ?: '';
    }

    public function statuses(): array
    {
        return $this->statuses;
    }

    /**
     * @param string|null $status
     * @return self|string
     */
    public function initial(string $status = null)
    {
        return ( is_null($status)
            ? $this->initialStatus
            : (function() use ($status) {
                $this->initialStatus = $status;
                return $this;
            })()
        );
    }

    public function metadata(array $metadata = null)
    {
        if(is_null($metadata)){
            return $this->metadata;
        } else {
            $this->metadata = $metadata;
            return null;
        }
    }
}