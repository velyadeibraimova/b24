<?php
    
    // код для активити PHP-код
    $rootActivity = $this->GetRootActivity();
    // $element_id = $rootActivity->GetVariable('ELEMENT_ID');
    $element_id = $rootActivity->GetDocumentId(); //id документа

    $list = LKIblock::getIblockAllFieldsWithFilter('APPROVE_STUFF_LIST', [], $element_id);
    $res = [];
    foreach ($list as $key => $element) {
        if(is_array($element['PROPS']['SOTRUDNIK']['VALUE'])){
           foreach ($element['PROPS']['SOTRUDNIK']['VALUE'] as $value) {
              $res[] = 'user_'.$value;
           }
        }else{
           $res[] =  'user_'.$element['PROPS']['SOTRUDNIK']['VALUE']; 
        }
    }

    $rootActivity->SetVariable("STUFF_LIST", $res);
    $rootActivity->SetVariable('SOGL_COL', count($res));

   
    // код для активити PHP-код в цикле (первый блок в цикле) 
    $rootActivity = $this->GetRootActivity();
    $list = $rootActivity->GetVariable("STUFF_LIST");
    $iterator = $rootActivity->GetVariable("ITERATION");
    $rootActivity->SetVariable('CURR_STUFF', $list[$iterator]);
    

    // активити  PHP-код (второй блок в цикле, после блока Утверждение документа - да) 
    $rootActivity = $this->GetRootActivity();
    $iterator = $rootActivity->GetVariable("ITERATION");
    $rootActivity->SetVariable('ITERATION', $iterator+1);
