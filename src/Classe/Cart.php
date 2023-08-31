<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack)
    {
        $this->session = $stack->getSession();
        $this->entityManager = $entityManager;
    }

    public function add($id)
    { // Je stock la session actuelle du panier dans la variable cart qui renvoie un tableau
        $cart = $this->session->get('cart', []);
        // si le panier a bien un produit inserer id specifique au produit 
        if (!empty($cart[$id])) {
            //rajoute une quantity    
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        return $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {
        /*Récuperation du cart*/
        $cart = $this->session->get('cart', []);

        if ($cart[$id] > 1) {
            //Retirer une quantité
            $cart[$id]--;
        } else {
            //suprimer le produit
            unset($cart[$id]);
        }

        return $this->session->set('cart', $cart);
    }

    public function getFull()
    {
        $cartComplete = [];

        if ($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

                if (!$product_object) {
                    $this->delete($id);
                    continue;
                }

                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartComplete;
    }
}
