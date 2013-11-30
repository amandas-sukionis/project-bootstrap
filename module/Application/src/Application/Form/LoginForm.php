<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class LoginForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('loginForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
        $this->setAttribute('method', 'post');
    }

    public function addFormInputs()
    {
        $emailInput = new Element\Text();
        $emailInput->setName('loginFormEmail');

        $passwordInput = new Element\Password();
        $passwordInput->setName('loginFormPassword');

        $csrf = new Element\Csrf('csrf');

        $submitButton = new Element\Submit();
        $submitButton
            ->setName('loginFormSubmit')
            ->setAttribute('id', 'loginFormSubmit');

        $this
            ->add($emailInput)
            ->add($passwordInput)
            ->add($csrf)
            ->add($submitButton);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $emailInput = new Input('loginFormEmail');
            $emailInput->setRequired(true);

            $passwordInput = new Input('loginFormPassword');
            $passwordInput->setRequired(true);

            $inputFilter
                ->add($emailInput)
                ->add($passwordInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}