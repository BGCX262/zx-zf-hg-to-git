<?php
return array(
	Zend_Validate_NotEmpty::IS_EMPTY => 'поле пустое, заполните его, пожалуйста',
	Zend_Validate_StringLength::TOO_SHORT => 'минимальная длина поля равна %min% символам',
	Zend_Validate_StringLength::TOO_LONG => 'максимальная длина поля равна %max% символам',
	Zend_Validate_EmailAddress::INVALID =>  "'%value%' - неверное значение поля",
	Zend_Validate_Regex::NOT_MATCH => ",'%value%' - значение поля не соответствует его шаблону",# 'a-zA-Z-_/'
	Zend_Validate_Alpha::NOT_ALPHA => ', должны быть только латинские буквы',
	Zend_Validate_Alpha::STRING_EMPTY => 'поле пустое, заполните его, пожалуйста',
	Zend_Captcha_Word::MISSING_VALUE => 'Не указаны символы на иллюстрации',
	Zend_Captcha_Word::MISSING_ID => 'Ошибка проверки символов на иллюстрации ',
	Zend_Captcha_Word::BAD_CAPTCHA => 'Неверно указаны символы на иллюстрации',
	Zx_Validate_PasswordConfirmation::NOT_MATCH => 'Поля "Пароль" и "Подтверждение пароля" различаются!',
);