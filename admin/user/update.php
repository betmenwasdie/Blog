<?php
#===============================================================================
# DEFINE: Administration
#===============================================================================
define('ADMINISTRATION', TRUE);
define('AUTHENTICATION', TRUE);

#===============================================================================
# INCLUDE: Main configuration
#===============================================================================
require '../../core/application.php';

#===============================================================================
# TRY: User\Exception
#===============================================================================
try {
	$User = User\Factory::build(HTTP::GET('id'));
	$Attribute = $User->getAttribute();

	if(HTTP::issetPOST('slug', 'username', 'password', 'fullname', 'mailaddr', 'body', 'time_insert', 'time_update', 'update')) {
		$Attribute->set('slug',     HTTP::POST('slug') ? HTTP::POST('slug') : makeSlugURL(HTTP::POST('username')));
		$Attribute->set('username', HTTP::POST('username') ? HTTP::POST('username') : NULL);
		$Attribute->set('password', HTTP::POST('password') ? password_hash(HTTP::POST('password'), PASSWORD_BCRYPT, ['cost' => 10]) : FALSE);
		$Attribute->set('fullname', HTTP::POST('fullname') ? HTTP::POST('fullname') : NULL);
		$Attribute->set('mailaddr', HTTP::POST('mailaddr') ? HTTP::POST('mailaddr') : NULL);
		$Attribute->set('body',     HTTP::POST('body') ? HTTP::POST('body') : NULL);
		$Attribute->set('time_insert', HTTP::POST('time_insert') ? HTTP::POST('time_insert') : date('Y-m-d H:i:s'));
		$Attribute->set('time_update', HTTP::POST('time_update') ? HTTP::POST('time_update') : date('Y-m-d H:i:s'));

		if(HTTP::issetPOST(['token' => Application::getSecurityToken()])) {
			try {
				if($Attribute->databaseUPDATE($Database)) {
				}
			} catch(PDOException $Exception) {
				$messages[] = $Exception->getMessage();
			}
		}

		else {
			$messages[] = $Language->text('error_security_csrf');
		}
	}

#===============================================================================
# TRY: Template\Exception
#===============================================================================
	try {
		$FormTemplate = Template\Factory::build('user/form');
		$FormTemplate->set('FORM', [
			'TYPE' => 'UPDATE',
			'INFO' => $messages ?? [],
			'DATA' => [
				'ID'       => $Attribute->get('id'),
				'SLUG'     => $Attribute->get('slug'),
				'USERNAME' => $Attribute->get('username'),
				'PASSWORD' => NULL,
				'FULLNAME' => $Attribute->get('fullname'),
				'MAILADDR' => $Attribute->get('mailaddr'),
				'BODY'     => $Attribute->get('body'),
				'TIME_INSERT' => $Attribute->get('time_insert'),
				'TIME_UPDATE' => $Attribute->get('time_update'),
			],
			'TOKEN' => Application::getSecurityToken()
		]);

		$InsertTemplate = Template\Factory::build('user/update');
		$InsertTemplate->set('HTML', $FormTemplate);

		$MainTemplate = Template\Factory::build('main');
		$MainTemplate->set('NAME', $Language->text('title_user_update'));
		$MainTemplate->set('HTML', $InsertTemplate);
		echo $MainTemplate;
	}

#===============================================================================
# CATCH: Template\Exception
#===============================================================================
	catch(Template\Exception $Exception) {
		Application::exit($Exception->getMessage());
	}
}

#===============================================================================
# CATCH: User\Exception
#===============================================================================
catch(User\Exception $Exception) {
	Application::error404();
}
?>