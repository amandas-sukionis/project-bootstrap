<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\Input;
use Zend\Validator;
use Zend\Validator\File\MimeType;
use Zend\InputFilter;

class SaveImageForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('saveImageForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
    }

    public function addFormInputs()
    {
        $name = new Element\Text('name');

        $shortDescription = new Element\Text('shortDescription');

        $this
            ->add($name)
            ->add($shortDescription);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter\InputFilter();

            $nameInput = new Input('name');
            $nameInput->setRequired(false);

            $shortDescriptionInput = new Input('shortDescription');
            $shortDescriptionInput->setRequired(false);

            $inputFilter
                ->add($nameInput)
                ->add($shortDescriptionInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}