<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class AlbumForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('addAlbumForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
        $this->setAttribute('method', 'post');
    }

    public function addFormInputs()
    {
        $nameInput = new Element\Text();
        $nameInput
            ->setName('name');

        $aliasInput = new Element\Text();
        $aliasInput
            ->setName('alias');

        $locationInput = new Element\Text();
        $locationInput
            ->setName('location')
            ->setAttribute('id', 'location');

        $shortDescriptionInput = new Element\Textarea();
        $shortDescriptionInput
            ->setName('shortDescription');

        $fullDescriptionInput = new Element\Textarea();
        $fullDescriptionInput
            ->setName('fullDescription');


        $submitButton = new Element\Submit();
        $submitButton
            ->setName('addAlbumFormSubmit');

        $this
            ->add($nameInput)
            ->add($aliasInput)
            ->add($locationInput)
            ->add($shortDescriptionInput)
            ->add($fullDescriptionInput)
            ->add($submitButton);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $nameInput = new Input('name');
            $nameInput->setRequired(true);

            $aliasInput = new Input('alias');
            $aliasInput->setRequired(false);

            $locationInput = new Input('location');
            $locationInput->setRequired(false);

            $shortDescriptionInput = new Input('shortDescription');
            $shortDescriptionInput->setRequired(false);

            $fullDescriptionInput = new Input('fullDescription');
            $fullDescriptionInput->setRequired(false);

            $inputFilter
                ->add($nameInput)
                ->add($aliasInput)
                ->add($locationInput)
                ->add($shortDescriptionInput)
                ->add($fullDescriptionInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}