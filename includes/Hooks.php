<?php

namespace MediaWiki\Extension\MultiTitle;

use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;
use MediaWiki\Page\Hook\ArticleViewFooterHook;
use MediaWiki\Page\Hook\ArticleViewRedirectHook;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\Title;

class Hooks implements GetDoubleUnderscoreIDsHook, ArticleViewRedirectHook, ArticleViewFooterHook {

	public function __construct( private readonly PageProps $pageProps ) {
	}

	/** @inheritDoc */
	public function onGetDoubleUnderscoreIDs( &$ids ): void {
		$ids[] = 'keeptitle';
	}

	/**
	 * Get the given property of a page, if it exists and has the property, or null otherwise.
	 */
	private function getPageProperty( Title|null $title, string $propertyName ): ?string {
		if ( !$title ) {
			return null;
		}
		$properties = $this->pageProps->getProperties( $title, $propertyName );
		return $properties[$title->getId()] ?? null;
	}

	/** @inheritDoc */
	public function onArticleViewRedirect( $article ): bool {
		return $this->getPageProperty( $article->getRedirectedFrom(), 'keeptitle' ) === null;
	}

	/** @inheritDoc */
	public function onArticleViewFooter( $article, $patrolFooterShown ): void {
		$redirectTitle = $article->getRedirectedFrom();
		if ( $this->getPageProperty( $redirectTitle, 'keeptitle' ) === null ) {
			return;
		}

		$outputPage = $article->getContext()->getOutput();
		$redirectDisplayTitle = $this->getPageProperty( $redirectTitle, 'displaytitle' )
			?? $redirectTitle->getPrefixedText();
		$outputPage->setPageTitle( $redirectDisplayTitle );
		$outputPage->setDisplayTitle( $redirectDisplayTitle );
	}
}
