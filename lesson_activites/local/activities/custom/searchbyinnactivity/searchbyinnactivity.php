<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPSearchByInnActivity extends BaseActivity
{
    // protected static $requiredModules = ["crm"];
    
    /**
     * @see parent::_construct()
     * @param $name string Activity name
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

        $fields = array("query" => $this->Inn, "count" => 5);
        $response = $dadata->suggest("party", $fields);
        
        $companyName = 'Компания не найдена!';
        if(!empty($response['suggestions'])){ // если копания найдена
           // по ИНН возвращается массив в котором может бытьнесколько элементов (компаний)
           $companyName = $response['suggestions'][0]['value']; // получаем имя компании из первого элемента  
        }  

        // в рабочем активити необходимо будет создать отдельный метод который будет получать результат ответа сервиса Dadata, 
        // обходить циклом результат и сохранять в массив все полученные организации

        $this->preparedProperties['Text'] = $companyName;
        $this->log($this->preparedProperties['Text']);

        /*
        $rootActivity = $this->GetRootActivity(); // получаем объект активити
        // сохранение полученных результатов работы активити в переменную бизнес процесса
        // $rootActivity->SetVariable("TEST", $this->preparedProperties['Text']); 

        // получение значения полей документа в активити        
        $documentType = $rootActivity->getDocumentType(); // получаем тип документа
        $documentId = $rootActivity->getDocumentId(); // получаем ID документа        
        // получаем объект документа над которым выполняется БП (элемент сущности Компания)
        $documentService = CBPRuntime::GetRuntime(true)->getDocumentService(); 
        // $documentService = $this->workflow->GetService("DocumentService");   

        // поля документа
        $documentFields =  $documentService->GetDocumentFields($documentType);
        // $arDocumentFields = $documentService->GetDocument($documentId);   

        foreach ($documentFields as $key => $value) {
            if($key == 'UF_CRM_1718872462762'){ // поле номер ИНН
                $fieldValue = $documentService->getFieldValue($documentId, $key, $documentType);
                $this->log('значение поля Инн:'.' '.$fieldValue);
            }

            if($key == 'UF_CRM_TEST'){ // поле TEST
                $fieldValue = $documentService->getFieldValue($documentId, $key, $documentType);
                $this->log('значение поля TEST:'.' '.$fieldValue);
            }
        }*/

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