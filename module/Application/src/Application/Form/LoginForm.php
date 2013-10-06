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
        $usernameInput = new Element\Text();
        $usernameInput
            ->setName('loginFormUsername');

        $passwordInput = new Element\Password();
        $passwordInput
            ->setName('loginFormPassword');


        $submitButton = new Element\Submit();
        $submitButton
            ->setName('loginFormSubmit')
            ->setAttribute('id', 'loginFormSubmit');

        $this
            ->add($usernameInput)
            ->add($passwordInput)
            ->add($submitButton);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $usernameInput = new Input('loginFormUsername');
            $usernameInput->setRequired(true);

            $passwordInput = new Input('loginFormPassword');
            $passwordInput->setRequired(true);

            $inputFilter
                ->add($usernameInput)
                ->add($passwordInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}