<?php
namespace SmartBuilder;

/**
 * Base module class for Smart Builder extensions
 *
 * @author Abdullah Al-Mesbahi <abdullah@cadr.sa>
 * @since 1.0.0
 */
class SmartBuilder extends \yii\base\Module
{

	/**
	 * @var array the the internalization configuration for this widget
	 */
	public $i18n = [];

	/**
	 * @var string translation message file category name for i18n
	 */
	protected $_msgCat = '';

	public function init() {

        parent::init();
		$this->_msgCat = 'app';
		$this->initI18N();
	}

	/**
	 * Yii i18n messages configuration for generating translations
	 *
	 * @return void
	 */
	public function initI18N($dir = '')
	{
		if (empty($this->_msgCat)) {
			return;
		}
		if (empty($dir)) {
			$reflector = new \ReflectionClass(get_class($this));
			$dir = dirname($reflector->getFileName());
		}
		Yii::setAlias("@{$this->_msgCat}", $dir);
		if (empty($this->i18n)) {
			$this->i18n = [
				'class' => 'yii\i18n\PhpMessageSource',
				'basePath' => "@{$this->_msgCat}/messages",
				'forceTranslation' => true
			];
		}
		Yii::$app->i18n->translations[$this->_msgCat . '*'] = $this->i18n;
	}
}
