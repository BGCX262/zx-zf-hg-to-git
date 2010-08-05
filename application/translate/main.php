<?php
return array(
	Zend_Captcha_Word::MISSING_VALUE => 'Не указаны символы на иллюстрации',
	Zend_Captcha_Word::MISSING_ID => 'Ошибка проверки символов на иллюстрации ',
	Zend_Captcha_Word::BAD_CAPTCHA => 'Неверно указаны символы на иллюстрации',

	Zend_Validate_Alpha::NOT_ALPHA => ', должны быть только латинские буквы',
	Zend_Validate_Alpha::STRING_EMPTY => 'поле пустое, заполните его, пожалуйста',
	Zend_Validate_Alnum::INVALID      => "Неверный тип данных, требуется строка или число",
	Zend_Validate_Alnum::NOT_ALNUM    => "'%value%' содержит не только буквы и цифры, как требуется",
	Zend_Validate_Alnum::STRING_EMPTY => "'%value%' - пустая строка",
	Zend_Validate_Callback::INVALID_VALUE => "'%value%' - данное значение неверное, исправьте его, пожалуйста",
	Zend_Validate_EmailAddress::INVALID =>  "'%value%' - неверное значение поля",
	Zend_Validate_File_Size::TOO_BIG   => "Максимально допустимый размер файла '%value%' равен '%max%', но Вы пытаетесь загрузить '%size%'",
    Zend_Validate_File_Size::TOO_SMALL => "Минимально допустимый размер файла '%value%' равен '%min%', но Вы пытаетесь загрузить  '%size%'",
    Zend_Validate_File_Size::NOT_FOUND => "Файл '%value%' не найден",
	Zend_Validate_NotEmpty::IS_EMPTY => 'поле пустое, заполните его, пожалуйста',
	Zend_Validate_StringLength::TOO_SHORT => 'минимальная длина поля равна %min% символам',
	Zend_Validate_StringLength::TOO_LONG => 'максимальная длина поля равна %max% символам',
	Zend_Validate_Regex::NOT_MATCH => ",'%value%' - значение поля не соответствует его шаблону",# 'a-zA-Z-_/'

	Zx_Validate_PasswordConfirmation::NOT_MATCH => 'Поля "Пароль" и "Подтверждение пароля" различаются!',
);