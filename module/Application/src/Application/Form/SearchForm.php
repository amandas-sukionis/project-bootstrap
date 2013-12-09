<?php
namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class SearchForm extends Form
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('searchForm');
        $this->addFormInputs();
        $this->setInputFilter($this->getMyInputFilter());
        $this->setAttribute('method', 'post');
    }

    public function addFormInputs()
    {
        $searchInput = new Element\Text();
        $searchInput->setName('searchFormWords');

        $csrf = new Element\Csrf('csrf');

        $submitButton = new Element\Submit();
        $submitButton
            ->setName('searchFormSubmit')
            ->setAttribute('id', 'searchFormSubmit');

        $this
            ->add($searchInput)
            ->add($csrf)
            ->add($submitButton);
    }

    public function getMyInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $searchInput = new Input('searchFormWords');
            $searchInput->setRequired(true);

            $inputFilter
                ->add($searchInput);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}