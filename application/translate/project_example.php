<?php
return array(
	#Zend_Validate_NotEmpty::IS_EMPTY => 'поле пустое!, заполните его, пожалуйста',
);
<?php
require PATH_FW . 'Zx/application/translate/main.php';
$translate_project = array(
	#Zend_Validate_NotEmpty::IS_EMPTY => 'поле пустое!, заполните его, пожалуйста',
);
if (!empty($translate_project)) {
	$translate = my_array_merge_recursive($translate, $translate_project);
}
// jEdit :indentSize=4:tabSize=4:noTabs=false:lineSeparator=\n:mode=php: