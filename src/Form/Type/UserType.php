<?php
namespace WebLinks\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('username', 'text')
				->add('password', 'repeated', [
					'type' 			  => 'password',
					'invalid_message' => 'The password fields must match.',
					'options'		  => ['required' => true],
					'first_options'   => ['label' => 'Password'],
					'second_options'  => ['label' => 'Repeat password']
				])
				->add('role', 'choice', [
					'choices' => [
						'ROLE_ADMIN' => 'Admin',
						'ROLE_USER'  => 'User'
					]
				]);
	}
	
	public function getName()
	{
		return 'user';
	}
}