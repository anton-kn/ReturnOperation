<?php

namespace NW\WebService\References\Operations\Notification;


/**
 * Summary of TemplateData
 */
class TemplateData {

    // посредник
    public int $resellerId;
    // клиент
    public int $clientId;
    // автор - создатель
    public int $creatorId;
    // cпециалист
    public int $expertId;
    // id жалобы
    public int $complaintId;
    // номер жалобы
    public string $complaintNumber;
    // id расхода
    public int $consumptionId;
    // номер расхода
    public string $consumptionNumber;
    // номер договора
    public string $agreementNumber;
    // Дата
    public string $date;
    // разногласие
    public array $differences;

    // тип уведомления
    public int $notificationType;

    public $client;

    /**
     * Автор
     * @var Employee
     */
    public $creator;

    /**
     * Эксперт
     * @var Employee
     */
    public $expert;

    /**
     * Данные запроса
     */
    public function formRequest($data): TemplateData
    {
        if(isset($data['resellerId'])){
            $this->resellerId = (int) $data['resellerId'];
        }
        if(isset($data['clientId'])){
            $this->clientId = (int) $data['clientId'];
        }
        if(isset($data['creatorId'])){
            $this->creatorId = (int) $data['creatorId'];
        }
        if(isset($data['expertId'])){
            $this->expertId = (int) $data['expertId'];
        }
        if(isset($data['complaintId'])){
            $this->complaintId = (int) $data['complaintId'];
        }
        if(isset($data['complaintNumber'])){
            $this->complaintNumber = (string) $data['complaintNumber'];
        }
        if(isset($data['consumptionId'])){
            $this->consumptionId = (int) $data['consumptionId'];
        }
        if(isset($data['consumptionNumber'])){
            $this->consumptionNumber = (string) $data['consumptionNumber'];
        }
        if(isset($data['agreementNumber'])){
            $this->agreementNumber = (string) $data['agreementNumber'];
        }
        if(isset($data['date'])){
            $this->date = (string) $data['date'];
        }
        if(isset($data['differences'])){
            $this->differences = (string) $data['differences'];
        }
        if(isset($data['notificationType'])){
            $this->notificationType = (int) $data['notificationType'];
        }

        return $this;
    }

    public function template(): array
    {
        // шаблон жалобы
        return [
            'COMPLAINT_ID'       => $this->complaintId, 
            'COMPLAINT_NUMBER'   => $this->complaintNumber,
            'CREATOR_ID'         => $this->creatorId,
            'CREATOR_NAME'       => $this->creator->getFullName(),
            'EXPERT_ID'          => $this->expertId,
            'EXPERT_NAME'        => $this->expert->getFullName(),
            'CLIENT_ID'          => $this->clientId,
            'CLIENT_NAME'        => $this->client->getFullName(),
            'CONSUMPTION_ID'     => $this->consumptionId,
            'CONSUMPTION_NUMBER' => $this->consumptionNumber,
            'AGREEMENT_NUMBER'   => $this->agreementNumber,
            'DATE'               => $this->date,
            'DIFFERENCES'        => $this->differences,
        ];
    }

}