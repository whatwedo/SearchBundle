<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Company;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Contact;
use whatwedo\SearchBundle\Tests\Fixtures\Factory\ContactFactory;

class IndexListenerTest extends AbstractSearchTest
{
    public function testEntityCreation()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertSame(0, $em->getRepository(Index::class)->count([]));

        /** @var Contact $contact */
        $contact = ContactFactory::createOne()->object();

        $indexResults = $em->getRepository(Index::class)->findAll();
        $this->assertSame(5, count($indexResults));

        /** @var Index $indexResult */
        foreach ($indexResults as $indexResult) {
            if ($indexResult->getModel() === Company::class) {
                $value = null;
                switch ($indexResult->getField()) {
                    case 'name':
                        $value = $contact->getCompany()->getName();
                        break;
                    case 'city':
                        $value = $contact->getCompany()->getCity();
                        break;
                    case 'country':
                        $value = $contact->getCompany()->getCountry();
                        break;
                    case 'taxIdentificationNumber':
                        $value = $contact->getCompany()->getTaxIdentificationNumber();
                        break;
                }

                $this->assertSame($value, $indexResult->getContent());
            }
        }
    }

    public function testEntityUpdate()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** @var Contact $contact */
        $contact = ContactFactory::createOne()->object();

        $contactId = $contact->getId();

        $em->clear();

        $contact = $em->getRepository(Contact::class)->find($contactId);

        $contact->getCompany()->setName('company');
        $contact->getCompany()->setCity('city');
        $contact->getCompany()->setCountry('county');
        $contact->getCompany()->setTaxIdentificationNumber('123456');

        $em->flush();
        $em->clear();

        $indexResults = $em->getRepository(Index::class)->findAll();

        /** @var Index $indexResult */
        foreach ($indexResults as $indexResult) {
            if ($indexResult->getModel() === Company::class) {
                $value = null;
                switch ($indexResult->getField()) {
                    case 'name':
                        $value = $contact->getCompany()->getName();
                        break;
                    case 'city':
                        $value = $contact->getCompany()->getCity();
                        break;
                    case 'country':
                        $value = $contact->getCompany()->getCountry();
                        break;
                    case 'taxIdentificationNumber':
                        $value = $contact->getCompany()->getTaxIdentificationNumber();
                        break;
                }

                $this->assertSame($value, $indexResult->getContent());
            }
        }
    }
}
