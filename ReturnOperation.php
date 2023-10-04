<?php

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\ResultNotification;
use NW\WebService\References\Operations\Notification\TemplateData;

/**
 * Данный класс обрабатывает жалобу клиента
 * 
 * 1. Приходят данные по жалобе
 * 2. Происходит валидация входных данных жалобы
 * 3. Если жалоба от клиента новая, то добавляем нового торгового посредника,
 * который будет заниматься этим вопросом.
 * 3.1 Если жалоба повторная, то торговый посредник тот же самый
 * 4. Заполняется жалоба
 * 5. Происходит проверка жалобы
 * 6. Если жалоба новая, отправляем сообщение клиенту (сформированный документ жалобы и кто этим занимается из торгового посредника)
 * 6.1. Если жалоба повторная, отправляем сообщение клиенту (сформированный документ жалобы и кто этим занимается из торгового посредника) и смс 
 */
class ReturnOperation extends ReferencesOperation
{
    /**
     * Новое сообщение
     */
    public const TYPE_NEW = 1;

    /**
     * Проверяем входные данные
     */
    private function validate(TemplateData $data){
        if (empty($data->resellerId)) { // нет торгового посредника
            throw new \Exception('Empty resellerId'); // сообщение по смс
        }
        if (empty($data->notificationType)) { // нет способа уведомления
            throw new \Exception('Empty notificationType', 400);
        }
        // находим продавца (посредника)
        if (Seller::getById($data->resellerId) === null) {
            throw new \Exception('Seller not found!', 400);
        }
        $data->client = Contractor::getById($this->data); // находим клента
        if ($data->client === null || $data->client->type !== Contractor::TYPE_CUSTOMER || $data->client->Seller->id !== $data->resellerId) {
            throw new \Exception('сlient not found!', 400);
        }
        $data->creator= Employee::getById($data->creatorId); // работник - автор
        if ($data->creator === null) {
            throw new \Exception('Creator not found!', 400);
        }
        $data->expert = Employee::getById($data->expertId); // работник - эксперт
        if ($data->expert === null) {
            throw new \Exception('Expert not found!', 400);
        }
    }

    /**
     * Установление разногласия и выбор торгового посредника
     */
    private function setDifferences(TemplateData $data){
        // если жалоба от клиента новая, то добавляем нового торгового посредника,
        // который будет заниматься этим вопросом.
        //
        // если жалоба повторная, то торговый посредник тот же самый
        $data->differences = null; // данные о разногласии (некий документ)
        // если новое сообщение
        if ($data->notificationType === self::TYPE_NEW) {
            // добавлена новая должность
            $data->differences = __('NewPositionAdded', null, $data->resellerId); // добавить нового торгового посредника
        } elseif ($data->notificationType === self::TYPE_CHANGE && !empty($data->differences)) {
            // статус позиции изменился
            $data->differences = __('PositionStatusHasChanged', [
                'FROM' => Status::getName($data->differences['from']), // от кого
                'TO'   => Status::getName($data->differences['to']),// кому
            ], $data->resellerId);
        }
    }

    
    /**
     * Выполнить операцию
     * @throws \Exception
     * @return void
     */
    public function doOperation(): void
    {
        $data = new TemplateData();
        $data->formRequest($this->getRequest('data'));
        
        $result = new ResultNotification();
        
        // проверяем данные
        $this->validate($data);

        // устанавливаем, кто будет заниматься вопросом
        $this->setDifferences($data);

        // Если хоть одна переменная для шаблона не задана, то не отправляем уведомления
        // проверка шаблона
        foreach ($data->template() as $key => $tempData) {
            if (empty($tempData)) {
                throw new \Exception("Template Data ({$key}) is empty!", 500);
            }
        }

        // Получаем сообщение торгового посредника
        $emailFrom = getResellerEmailFrom();
        // Получаем email сотрудников из настроек
        $emails = getEmailsByPermit($data->resellerId, 'tsGoodsReturn'); // возврат товара
        // если жалоба новая, то отправляем сообщение клиенту
        // сформированный документ жалобы и кто этим занимается из торгового посредника
        if ($data->notificationType === self::TYPE_NEW) {
            if (!empty($emailFrom) && count($emails) > 0) {
                foreach ($emails as $email) {
                    MessagesClient::sendMessage([ // сообщение клиенту 
                        MessageTypes::EMAIL => [ // MessageTypes::EMAIL
                            'emailFrom' => $emailFrom,
                            'emailTo'   => $email,
                            'subject'   => __('complaintEmployeeEmailSubject', $data->template(), $data->resellerId),
                            'message'   => __('complaintEmployeeEmailBody', $data->template(), $data->resellerId),
                        ],
                    ], $data->resellerId, NotificationEvents::NEW_RETURN_STATUS);
    
                    // сообщение клиенту отправлено
                    $result->setClientByEmail(true);
                }
            }
        }

        // Если произошли изменения статуса жалобы, то отправляем сообщение и смс клиенту
        if ($data->notificationType === self::TYPE_CHANGE && !empty($data->differences['to'])) {
            if (!empty($emailFrom) && !empty($client->email)) {
                MessagesClient::sendMessage([
                    MessageTypes::EMAIL => [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo'   => $data->client->email,
                        'subject'   => __('complaintClientEmailSubject', $data->template(), $data->resellerId),
                        'message'   => __('complaintClientEmailBody', $data->template(), $data->resellerId),
                    ],
                ], $data->resellerId, $data->client->id, NotificationEvents::CHANGE_RETURN_STATUS, $data->differences['to']);
                
                $result->setClientByEmail(true);
            }

            if (isset($client->mobile) && !empty($client->mobile)) {
                // отправляем уведомление менеджеру
                $res = NotificationManager::send($data->resellerId, $data->client->id, NotificationEvents::CHANGE_RETURN_STATUS, $data->differences['to'], $data->template(), $error);

                if ($res) {
                    $result->setClientBySms(true);
                }
                if (!empty($error)) {
                    $result->setClientBySmsError($error);
                }
            }
        }

        return $result->getResult();
    }
}
