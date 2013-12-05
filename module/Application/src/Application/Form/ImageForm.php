<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\Input;
use Zend\Validator;
use Zend\Validator\File\MimeType;
use Zend\InputFilter;

class ImageForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('imageForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
    }

    public function addFormInputs()
    {
        $name = new Element\Text('name');

        $shortDescription = new Element\Text('shortDescription');

        $isAlbumImageInput = new Element\Radio('isAlbumImage');
        $isAlbumImageInput->setValueOptions(
            [
            '0' => 'Ne',
            '1' => 'Taip',
            ]
        );
        $isAlbumImageInput->setChecked('0');
        $isAlbumImageInput->setLabelAttributes(['class' => 'radio-inline']);

        $isPublicInput = new Element\Radio('isPublic');
        $isPublicInput->setValueOptions(
            [
            '0' => 'Ne',
            '1' => 'Taip',
            ]
        );
        $isPublicInput->setChecked('0');
        $isPublicInput->setLabelAttributes(['class' => 'radio-inline']);

        $submitButton = new Element\Submit();
        $submitButton
            ->setName('addImageFormSubmit');

        $this
            ->add($name)
            ->add($isAlbumImageInput)
            ->add($isPublicInput)
            ->add($isAlbumImageInput)
            ->add($submitButton)
            ->add($shortDescription);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter\InputFilter();

            $nameInput = new Input('name');
            $nameInput->setRequired(false);

            $isAlbumImageInput = new Input('isAlbumImage');
            $isAlbumImageInput->setRequired(false);

            $isPublicInput = new Input('isPublic');
            $isPublicInput->setRequired(false);

            $shortDescriptionInput = new Input('shortDescription');
            $shortDescriptionInput->setRequired(false);

            $inputFilter
                ->add($nameInput)
                ->add($isAlbumImageInput)
                ->add($isPublicInput)
                ->add($shortDescriptionInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}