<?php
namespace WebLinks\DAO;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use WebLinks\Domain\User;

class UserDAO extends DAO implements UserProviderInterface
{
	/**
	 * Returns a user matching the supplied id.
	 * @param integer $id The user id
	 * @return \Webllinks\Domain\User | throws an axception if no matching user is found
	 */
	public function find($id)
	{
		$sql = 'SELECT * FROM t_user WHERE user_id = ?';
		$row = $this->getDb()->fetchAssoc($sql, [$id]);
		
		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception('No user matching id ' . $id);
	}
	
	/**
	 * Returns a list of all users, sorted by role and name.
	 * @return array Alist of all users
	 */
	public function findAll()
	{
		$sql = 'SELECT * FROM t_user ORDER BY user_role, user_name';
		$result = $this->getDb()->fetchAll($sql);
		
		// Convert query result to an array of domain objects
		$entities = [];
		
		foreach ($result as $row)
		{
			$id = $row['user_id'];
			$entities[$id] = $this->buildDomainObject($row);
		}
		
		return $entities;
	}	
	
	/**
	 * {@inheritDoc}
	 */
	public function loadUserByUsername($username)
	{
		$sql = 'SELECT * FROM t_user WHERE user_name = ?';
		$row = $this->getDb()->fetchAssoc($sql, [$username]);
		
		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		
		if (!$this->supportsClass($class))
		{
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
		}
		
		return $this->loadUserByUsername($user->getUsername());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function supportsClass($class)
	{
		return 'WebLinks\Domain\User' === $class;
	}
	
	/**
	 * Saves a user into the database
	 * @param \WebLinks\Domain\User $user The user to save
	 */
	public function save(User $user)
	{
		$userData = [
			'user_name' => $user->getUsername(),
			'user_salt' => $user->getSalt(),
			'user_password' => $user->getPassword(),
			'user_role' => $user->getRole()
		];
		
		if ($user->getId())
		{
			// The user has already been saved : update it
			$this->getDb()->update('t_user', $userData, ['user_id' => $user->getId()]);
		}
		else
		{
			// The user has never been saved : insert it
			$this->getDb()->insert('t_user', $userData);
			
			// Get the id of the newly created user and set it on the entity
			$id = $this->getDb()->lastInsertId();
			$user->setId($id);
		}
	}
	
	/**
	 * Removes a user from the database
	 * @param integer $id The user id
	 */
	public function delete($id)
	{
		// Delete the user
		$this->getDb()->delete('t_user', ['user_id' => $id]);
	}
	
	/**
	 * Creates a User object based on a DB row
	 * @param array $row The DB row containing User data
	 * @return \WebLinks\Domain\User
	 */
	protected function buildDomainObject($row)
	{
		$user = new User();
		$user->setId($row['user_id']);
		$user->setUsername($row['user_name']);
		$user->setPassword($row['user_password']);
		$user->setSalt($row['user_salt']);
		$user->setRole($row['user_role']);
		
		return $user;
	}
}
