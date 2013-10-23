<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\Input;
use Zend\Validator;
use Zend\Validator\File\MimeType;
use Zend\InputFilter;

class UploadImageForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('uploadImageForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
    }

    public function addFormInputs()
    {
        $imageFile = new Element\File('uploadImageFile');
        $imageFile
            ->setAttribute('id', 'uploadImageFile')
            ->setAttribute('multiple', true);

        $csrf = new Element\Csrf('csrf');

        $submitButton = new Element\Submit();
        $submitButton
            ->setName('uploadImageFormSubmit')
            ->setAttribute('id', 'uploadImageFormSubmit');

        $this
            ->add($imageFile)
            ->add($csrf)
            ->add($submitButton);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter\InputFilter();

            $imageFile = new InputFilter\FileInput('uploadImageFile');
            $imageFile->setRequired(true);
            $imageFile->getValidatorChain() //->addValidator(new \Zend\Validator\File\MimeType('image/png,image/jpg'));
                ->attachByName('filemimetype', array('mimeType' => 'image/png,image/jpg,image/jpeg'))
                ->attachByName('filesize', array('max' => 5242880));

            $inputFilter
                ->add($imageFile);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}