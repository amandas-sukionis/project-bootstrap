<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Validator\File\MimeType;

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
        $this
            ->setAttribute('method', 'post');

        $imageFile = new Element\File();
        $imageFile
            ->setName('uploadImageFile')
            ->setAttribute('id', 'uploadImageFile');

        $submit = new Element\Submit();
        $submit
            ->setName('uploadImageFile')
            ->setAttribute('id', 'uploadImageFile')
            ->setValue('upload');

        $this
            ->add($imageFile)
            ->add($submit);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $imageFile = new Input('uploadImageFile');
            $imageFile->setRequired(true);
            $imageFile->getValidatorChain()->addValidator(new \Zend\Validator\File\MimeType('image/png,image/jpg'));

            $inputFilter
                ->add($imageFile);

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}