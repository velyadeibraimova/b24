<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Main\Diag\Debug;

class CBPSearchByInnActivity extends BaseActivity
{
    // protected static $requiredModules = ["crm"];

    /**
     * @param $name string Activity name
     * @see parent::_construct()
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Inn' => '',

            // return
            'Text' => null,
        ];

        $this->SetPropertiesTypes([
            'Text' => ['Type' => FieldType::STRING],
        ]);
    }

    /**
     * Return activity file path
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * @return ErrorCollection
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $token = "0c825d0906122684951a7a3d60ee8848289d4344";
        $secret = "db2700343995d8f5e1992e0fcbd81ded70267e71";

        // token и secret лучше передавать в виде переменных БП в активити
        // $rootActivity->GetVariable("TOKEN");
        // $rootActivity->GetVariable("SECRET");

        $dadata = new Dadata($token, $secret);
        $dadata->init();
        $response = $dadata->suggest("party", [
            "query" => $this->Inn,
            "count" => 1
        ]);
        $dadata->close();

        $companyName = 'Компания не найдена!';
        if (!empty($response)) { // если копания найдена
            $companyName = $response['suggestions'][0]['value']; // получаем имя компании из первого элемента
            try {
                $companyData = [
                    'TITLE' => $companyName,
                    "ASSIGNED_BY_ID" => 1,
                    'MODIFY_BY_ID' => 1
                ];

                $result = \Bitrix\Crm\CompanyTable::add($companyData);
                // Логируем результат создания компании
                if ($result->isSuccess()) {
                    $companyId = $result->getId();
                    $rootActivity = $this->GetRootActivity(); // получаем объект активити
                    $documentType = $rootActivity->getDocumentType();
                    $documentId = $rootActivity->getDocumentId(); // получаем ID документа
                    $documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
                    $documentFields = $documentService->GetDocumentFields($documentType);
                    //Debug::writeToFile(print_r($documentId, true), '$documentId', '/upload/dadata_log.txt');
                    foreach ($documentFields as $key => $value) {
                        if ($key == 'PROPERTY_ZAKAZCHIK') {
                            \CIBlockElement::SetPropertyValues(intval($documentId['2']), 19, $companyName, 'ZAKAZCHIK');
                        }
                    }
                    //Debug::writeToFile('Компания успешно создана. ID: ' . $companyId, 'Company created', '/upload/dadata_log.txt');
                } else {
                    //Debug::writeToFile('Ошибка создания компании: ' . implode(', ', $result->getErrorMessages()), 'Company create error', '/upload/dadata_log.txt');
                }
            } catch (Exception $e) {
                //Debug::writeToFile('Произошла ошибка: ' . $e->getMessage(), 'Exception', '/upload/dadata_log.txt');
            }
        } else {
            //Debug::writeToFile('Компания не найдена по ИНН', 'Company not found', '/upload/dadata_log.txt');
        }

        return $errors;
    }

    /**
     * @param PropertiesDialog|null $dialog
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        $map = [
            'Inn' => [
                'Name' => Loc::getMessage('SEARCHBYINN_ACTIVITY_FIELD_SUBJECT'),
                'FieldName' => 'inn',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
        ];
        return $map;
    }


}