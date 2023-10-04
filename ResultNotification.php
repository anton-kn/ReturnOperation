<?php

namespace NW\WebService\References\Operations\Notification;
/**
 * Данные по уведомлению
 */
class ResultNotification{

    /**
     * Статус сообщения сотруднику по электронной почте
     * @var bool
     */
    private bool $employeeByEmail;

    /**
     * Статус сообщения клиенту по электронной почте
     * @var bool
     */
    private bool $clientByEmail;

    /**
     * Статус сообщения клиенту по смс
     * @var bool
     */
    private bool $clientBySms;

    /**
     * Ошибка отправки смс
     * @var string
     */
    private string $clientBySmsError;

    /**
     * Результат по уведомлению
     * @return array
     */
    public function getResult(): array
    {
        return [
            'notificationEmployeeByEmail' => empty($this->employeeByEmail) ? $this->employeeByEmail : false,
            'notificationClientByEmail'   => empty($this->clientByEmail) ? $this->clientByEmail : false,
            'notificationClientBySms'     => [
                'isSent'  => empty($this->clientBySms) ? $this->clientBySms : false,
                'message' => empty($this->clientBySmsError) ? $this->clientBySmsError : '',
            ],
        ];
    }

	/**
	 * Статус сообщения сотруднику по электронной почте
	 * @return bool
	 */
	public function getEmployeeByEmail(): bool {
		return $this->employeeByEmail;
	}
	
	/**
	 * Статус сообщения сотруднику по электронной почте
	 * @param bool $employeeByEmail Статус сообщения сотруднику по электронной почте
	 * @return ResultNotification
	 */
	public function setEmployeeByEmail(bool $employeeByEmail): ResultNotification {
		$this->employeeByEmail = $employeeByEmail;
		return $this;
	}

	/**
	 * Статус сообщения клиенту по электронной почте
	 * @return bool
	 */
	public function getClientByEmail(): bool {
		return $this->clientByEmail;
	}
	
	/**
	 * Статус сообщения клиенту по электронной почте
	 * @param bool $clientByEmail Статус сообщения клиенту по электронной почте
	 * @return ResultNotification
	 */
	public function setClientByEmail(bool $clientByEmail): ResultNotification {
		$this->clientByEmail = $clientByEmail;
		return $this;
	}

	/**
	 * Статус сообщения клиенту по смс
	 * @return bool
	 */
	public function getClientBySms(): bool {
		return $this->clientBySms;
	}
	
	/**
	 * Статус сообщения клиенту по смс
	 * @param bool $clientBySms Статус сообщения клиенту по смс
	 * @return ResultNotification
	 */
	public function setClientBySms(bool $clientBySms): ResultNotification {
		$this->clientBySms = $clientBySms;
		return $this;
	}

	/**
	 * Ошибка отправки смс
	 * @return string
	 */
	public function getClientBySmsError(): string {
		return $this->clientBySmsError;
	}
	
	/**
	 * Ошибка отправки смс
	 * @param string $clientBySmsError Ошибка отправки смс
	 * @return ResultNotification
	 */
	public function setClientBySmsError(string $clientBySmsError): ResultNotification {
		$this->clientBySmsError = $clientBySmsError;
		return $this;
	}
}