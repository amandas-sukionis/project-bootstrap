<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\I18n\Validator\Alpha;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;

class RegisterForm extends Form
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
        $firstNameInput = new Element\Text();
        $firstNameInput->setName('firstName');

        $lastNameInput = new Element\Text();
        $lastNameInput->setName('lastName');

        $emailInput = new Element\Email();
        $emailInput->setName('email');

        $passwordInput = new Element\Password();
        $passwordInput->setName('password');

        $confirmPasswordInput = new Element\Password();
        $confirmPasswordInput->setName('confirmPassword');

        $submitButton = new Element\Submit();
        $submitButton->setName('userRegisterFormSubmit');

        $csrf = new Element\Csrf('csrf');

        $this
            ->add($firstNameInput)
            ->add($lastNameInput)
            ->add($emailInput)
            ->add($passwordInput)
            ->add($confirmPasswordInput)
            ->add($submitButton)
            ->add($csrf);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $firstNameInput = new Input('firstName');
            $firstNameInput->setRequired(true);
            $firstNameInput->getValidatorChain()
                ->attach(
                    new StringLength([
                                     'min' => 3,
                                     ])
                )
                ->attach(new Alpha());

            $lastNameInput = new Input('lastName');
            $lastNameInput->setRequired(true);
            $lastNameInput->getValidatorChain()
                ->attach(
                    new StringLength([
                                     'min' => 3,
                                     ])
                )
                ->attach(new Alpha());

            $emailInput = new Input('email');
            $emailInput->setRequired(true);
            $emailInput->getValidatorChain()
                ->attach(new EmailAddress());

            $passwordInput = new Input('password');
            $passwordInput->setRequired(true);
            $passwordInput->getValidatorChain();

            $confirmPasswordInput = new Input('confirmPassword');
            $confirmPasswordInput->setRequired(true);
            $confirmPasswordInput->getValidatorChain()
                ->attach(new Identical('password'));

            $inputFilter
                ->add($firstNameInput)
                ->add($lastNameInput)
                ->add($emailInput)
                ->add($passwordInput)
                ->add($confirmPasswordInput);

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}

