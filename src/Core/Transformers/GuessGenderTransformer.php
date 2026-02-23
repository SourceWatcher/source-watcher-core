<?php

namespace Coco\SourceWatcher\Core\Transformers;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Step\Transformer;

use GenderDetector\Country;
use GenderDetector\Gender;
use GenderDetector\GenderDetector;

/**
 *
 */
class GuessGenderTransformer extends Transformer
{
    /**
     * @var string
     */
    protected string $country;

    /**
     * @var string
     */
    protected string $firstNameField;

    /**
     * @var string
     */
    protected string $genderField;

    /**
     * GuessGenderTransformer constructor.
     */
    public function __construct ()
    {
        $this->country = Country::USA;
        $this->firstNameField = 'first_name';
        $this->genderField = 'gender';
    }

    /**
     * @var array|string[]
     */
    protected array $availableOptions = [ "country", "firstNameField", "genderField" ];

    /**
     * @param Row $row
     */
    public function transform ( Row $row )
    {
        $currentGender = $row->get( $this->genderField );

        if ( empty( $currentGender ) ) {
            $dictionaryPath = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'names-dictionary.txt';

            $genderDetector = new GenderDetector( $dictionaryPath );
            $genderDetector->setUnknownGender( Gender::UNISEX );

            $firstName = $row->get( $this->firstNameField );

            if ( !empty( $firstName ) ) {
                $gender = $genderDetector->detect( $firstName, $this->country );

                $row->set( $this->genderField, $gender );
            }
        }
    }
}
