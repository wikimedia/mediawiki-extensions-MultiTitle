<?php

namespace MediaWiki\Extension\MultiTitle\Tests\Integration;

use MediaWiki\Context\RequestContext;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Article;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\MultiTitle\Hooks
 * @group Database
 */
class MultiTitleTest extends MediaWikiIntegrationTestCase {
	private function viewPage( Title $title, ?Title $redirectFrom = null ): OutputPage {
		$context = RequestContext::getMain();
		$context->setTitle( $title );
		$article = Article::newFromTitle( $title, $context );
		if ( $redirectFrom !== null ) {
			$article->setRedirectedFrom( $redirectFrom );
		}
		$article->view();
		return $context->getOutput();
	}

	public function testNormalRedirect(): void {
		$this->insertPage( 'Cease', 'to stop' );
		$this->insertPage( 'Desist', '#REDIRECT [[Cease]]' );

		$output = $this->viewPage( Title::newFromText( 'Cease' ), Title::newFromText( 'Desist' ) );
		$this->assertStringContainsString( 'Cease', $output->getPageTitle() );
		$this->assertEquals( 'Desist', $output->getJSVars()["wgRedirectedFrom"] );
	}

	public function testKeeptitleRedirect(): void {
		$this->insertPage( 'Cease', 'to stop' );
		$this->insertPage( 'Desist', '#REDIRECT [[Cease]] __KEEPTITLE__' );

		$output = $this->viewPage( Title::newFromText( 'Cease' ), Title::newFromText( 'Desist' ) );
		$this->assertStringContainsString( 'Desist', $output->getPageTitle() );
		$this->assertArrayNotHasKey( "wgRedirectedFrom", $output->getJSVars() );
	}

	public function testKeeptitleWithDisplaytitle(): void {
		$this->insertPage( 'Cease', 'to stop' );
		$this->insertPage( 'Desist', '#REDIRECT [[Cease]] __KEEPTITLE__ {{DISPLAYTITLE:\'\'Desist\'\'}}' );

		$output = $this->viewPage( Title::newFromText( 'Cease' ), Title::newFromText( 'Desist' ) );
		$this->assertStringContainsString( '<i>Desist</i>', $output->getPageTitle() );
	}

	/* public function testKeeptitleRedirect(): void { */
	/* 	$this->insertPage( 'Cease', 'to stop' ); */
	/* } */
}
