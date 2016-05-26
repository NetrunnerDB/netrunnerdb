<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TranslationController extends Controller
{
	protected $translatableFields = [
			'NetrunnerdbCardsBundle:Side' => [ 'name' ],
			'NetrunnerdbCardsBundle:Faction' => [ 'name' ],
			'NetrunnerdbCardsBundle:Type' => [ 'name' ],
			'NetrunnerdbCardsBundle:Cycle' => [ 'name' ],
			'NetrunnerdbCardsBundle:Pack' => [ 'name' ],
			'NetrunnerdbCardsBundle:Card' => [ 'title', 'keywords', [ 'name' => 'text', 'type' => TextareaType::class ], [ 'name' => 'flavor', 'type' => TextareaType::class ] ]
	];
	

	/**
	 * Gets the name of the locale
	 * Returns null if the locale is not supported *for translations*
	 * @param string $localeCode
	 * @return string
	 */
	private function getLocaleName($localeCode)
	{
		if($localeCode === 'en') return;
		$localeNames = $this->getParameter('locale_names');
		$supportedLocales = $this->getParameter('supported_locales');
		if(!in_array($localeCode, $supportedLocales)) {
			return;
		}
		return key_exists($localeCode, $localeNames) ? $localeNames[$localeCode] : $localeCode;
	}

	/**
	 * Gets all the name of the supported locales for translation
	 * @param string $localeCode
	 * @return string
	 */
	private function getAllLocaleNames()
	{
		$localeNames = $this->getParameter('locale_names');
		$supportedLocales = $this->getParameter('supported_locales');
		$allLocalNames = [];
		foreach($supportedLocales as $supportedLocale) {
			if($supportedLocale === 'en') continue;
			$allLocalNames[$supportedLocale] = $localeNames[$supportedLocale];
		}
		return $allLocalNames;
	}
	
	public function indexAction(Request $request)
	{
		$this->denyAccessUnlessGranted('ROLE_TRANSLATOR', null, "Not a translator");
		
		return $this->render('NetrunnerdbCardsBundle:Translation:index.html.twig', array(
				"supportedLocales" => $this->getAllLocaleNames()
		));
	}
	
	public function overviewAction($localeCode, Request $request)
	{
		$this->denyAccessUnlessGranted('ROLE_TRANSLATOR', null, "Not a translator");
		
		$localeName = $this->getLocaleName($localeCode);
		if(!$localeName) {
			return $this->createNotFoundException("No such locale");
		}
		
		$entityManager = $this->getDoctrine()->getManager();
		$translator = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
		
		// name of the entity => number of translatable fields
		$entityList = [ 'Side' => 1, 'Faction' => 1, 'Type' => 1, 'Cycle' => 1, 'Pack' => 1, 'Card' => 4 ];
		
		// name of entity => ratio of translated fields (as percent)
		$entityTranslationRatioList = [];
		
		foreach($entityList as $entityName => $maxTranslatableFieldsPerRecord)
		{
			$translatedFields = 0;
			$maxTranslatedFields = 0;
			$records = $entityManager->getRepository('NetrunnerdbCardsBundle:'.$entityName)->findAll();
			foreach($records as $record)
			{
				$maxTranslatedFields += $maxTranslatableFieldsPerRecord;
				$translations = $translator->findTranslations($record);
				if(isset($translations[$localeCode])) {
					$translatedFields += count($translations[$localeCode]);
				}
			}
			$entityTranslationRatioList[$entityName] = round($translatedFields / $maxTranslatedFields * 100);
		}
		
		return $this->render('NetrunnerdbCardsBundle:Translation:overview.html.twig', array(
				"localeCode" => $localeCode,
				"localeName" => $localeName,
				"entityTranslationRatioList" => $entityTranslationRatioList
		));
	}
	
	public function entityIndexAction($localeCode, $entityName, Request $request)
	{
		$this->denyAccessUnlessGranted('ROLE_TRANSLATOR', null, "Not a translator");
		
		$localeName = $this->getLocaleName($localeCode);
		if(!$localeName) {
			return $this->createNotFoundException("No such locale");
		}
		
		$entityList = [ 'Side' => 1, 'Faction' => 1, 'Type' => 1, 'Cycle' => 1, 'Pack' => 1, 'Card' => 4 ];
		if(!key_exists($entityName, $entityList)) {
			return $this->createNotFoundException("No such entity");
		}
		$maxTranslatableFieldsPerRecord = $entityList[$entityName];

		$entityManager = $this->getDoctrine()->getManager();
		$translator = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
		
		// array of [ record, ratio ]
		$recordsTranslationList = [];
		
		$records = $entityManager->getRepository('NetrunnerdbCardsBundle:'.$entityName)->findAll();
		foreach($records as $record)
		{
			$translatedFields = 0;
			$translations = $translator->findTranslations($record);
			if(isset($translations[$localeCode])) {
				$translatedFields = count($translations[$localeCode]);
			}
			$recordsTranslationList[] = [
					'record' => $record,
					'ratio' => round($translatedFields / $maxTranslatableFieldsPerRecord * 100),
					];
		}
		
		ksort($recordsTranslationList);
		
		return $this->render('NetrunnerdbCardsBundle:Translation:entityIndex.html.twig', array(
				"localeCode" => $localeCode,
				"localeName" => $localeName,
				"entityName" => $entityName,
				"recordsTranslationList" => $recordsTranslationList
		));
	}
	
	public function entityFormAction($localeCode, $entityName, $recordId, Request $request)
	{
		$this->denyAccessUnlessGranted('ROLE_TRANSLATOR', null, "Not a translator");
		
		$localeName = $this->getLocaleName($localeCode);
		if(!$localeName) {
			return $this->createNotFoundException("No such locale");
		}
		
		$entityList = [ 'Side' => 1, 'Faction' => 1, 'Type' => 1, 'Cycle' => 1, 'Pack' => 1, 'Card' => 4 ];
		if(!key_exists($entityName, $entityList)) {
			return $this->createNotFoundException("No such entity");
		}
		$maxTranslatableFieldsPerRecord = $entityList[$entityName];

		$entityManager = $this->getDoctrine()->getManager();
		/* @var $translator \Gedmo\Translatable\Document\Repository\TranslationRepository */
		$translator = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
		
		$className = 'NetrunnerdbCardsBundle:'.$entityName;
		
		/* @var $repository \Netrunnerdb\CardsBundle\Repository\TranslatableRepository */
		$repository = $entityManager->getRepository($className);
		
		$record = $repository->find($recordId);
		if(!$record) {
			return $this->createNotFoundException("No such record");
		}
		
		$translatableFields = $this->translatableFields[$className];
		
		$defaultLocaleValues = [];
		
		foreach($translatableFields as $translatableField) {
			if(is_array($translatableField)) {
				$translatableFieldName = $translatableField['name'];
				$translatableFieldType = $translatableField['type'];
			} else {
				$translatableFieldName = $translatableField;
				$translatableFieldType = TextType::class;
			}
				
			$getter = 'get'.ucfirst(strtolower($translatableFieldName));
			$defaultLocaleValues[$translatableFieldName] = $record->$getter();
		}
		
		$record->setTranslatableLocale($localeCode);
		$entityManager->refresh($record);
		
		$builder = $this->createFormBuilder($record);
		foreach($translatableFields as $translatableField) {
			if(is_array($translatableField)) {
				$translatableFieldName = $translatableField['name'];
				$translatableFieldType = $translatableField['type'];
			} else {
				$translatableFieldName = $translatableField;
				$translatableFieldType = TextType::class;
			}
			
			$builder->add('default_'.$translatableFieldName, $translatableFieldType, [ 'mapped' => false, 'disabled' => true, 'data' => $defaultLocaleValues[$translatableFieldName] ]);
			$builder->add($translatableFieldName, $translatableFieldType, [ 'required' => false ]);
		}
		$builder->add('save', SubmitType::class, array('label' => 'Save Translation'));
		$builder->add('saveAndNext', SubmitType::class, array('label' => 'Save and Open Next'));
		$form = $builder->getForm();
		
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			
			$record->setTranslatableLocale($localeCode);
			$entityManager->flush();
			
			if($form->get('saveAndNext')->isClicked()) {
				$sortField = 'id';
				$sortMethod = 'getId';
				if(method_exists($record, 'getCode')) {
					$sortField = 'code';
					$sortMethod = 'getCode';
				}
				// find next entity
				$qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
				$qb->select('c')
					->from($className, 'c')
					->where("c.$sortField > ?1")
					->orderBy("c.$sortField", 'ASC')
					->setParameter(1, $record->$sortMethod())
					->setMaxResults(1);
				$query = $qb->getQuery();
				$next = $query->getSingleResult();
				if($next) {
					return $this->redirectToRoute('translation_entity_form', ['localeCode'=>$localeCode, 'entityName' => $entityName, 'recordId' => $next->getId()]);
				}
			}
			
			return $this->redirectToRoute('translation_entity_index', ['localeCode'=>$localeCode, 'entityName' => $entityName]);
		}
		
		return $this->render('NetrunnerdbCardsBundle:Translation:entityForm.html.twig', array(
				"localeCode" => $localeCode,
				"localeName" => $localeName,
				"entityName" => $entityName,
				"recordId" => $recordId,
				"form" => $form->createView()
		));
	}
}