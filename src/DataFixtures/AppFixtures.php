<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    // pour hacher le mot de pass

    /**
     * l'encodeur de mots de pass
     *
     * @var UserPasswordHasherInterface
     */
    private $encoder;
    
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder= $encoder;
    }
    
    public function load(ObjectManager $manager): void
    {
        // instancier faker et parametrer sa locale en francais
        $faker = Factory::create('fr_FR');

       
        
        // je veux qu'1 utilisateur puisse créer entre 5 et 20 clients qui possédent entre 3 et 10 factures
        // fixtures des faux utilisateurs
        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            
            // incrémenter le chrono des factures pour chaque client
            $chrono = 1;
        
            // encoder le mot de pass utilisateur
            $hash=$this->encoder->encodePassword($user, "password");
            
            $user->setFirstName($faker->email)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setPassword($hash);

            $manager->persist($user);

            // fixtures des faux clients
            for ($i = 0; $i < mt_rand(5, 20); $i++) {
                $customer = new Customer();
                $customer->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName)
                    ->setEmail($faker->email)
                    ->setCompany($faker->company)
                    ->setUser($user);

                $manager->persist($customer);

                // fixtures des fausses factures
                for ($j = 0; $j < mt_rand(3, 10); $j++) {
                    $invoice = new Invoice();
                    $invoice->setAmount($faker->randomFloat(2, 250, 5000))
                        ->setSentAt($faker->dateTimeBetween('-6 months'))
                        ->setStatus($faker->randomElement(['SEND', 'PAID', 'CANCELLED']))
                        ->setCustomer(($customer))
                        ->setChrono(($chrono));

                    // autoincrémenter le numero de facture à chaque création de nouvelle facture
                    $chrono++;

                    $manager->persist(($invoice));
                }
            }
        }



        $manager->flush();
    }
}