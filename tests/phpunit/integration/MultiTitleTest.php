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
		$cease = Title::makeTitle( NS_MAIN, 'Cease' );
		$desist = Title::makeTitle( NS_MAIN, 'Desist' );
		$this->insertPage( $cease, 'to stop' );
		$this->insertPage( $desist, '#REDIRECT [[Cease]]' );

		$output = $this->viewPage( $cease, $desist );
		$this->assertStringContainsString( 'Cease', $output->getPageTitle() );
		$this->assertEquals( 'Desist', $output->getJSVars()["wgRedirectedFrom"] );
	}

	public function testKeeptitleRedirect(): void {
		$cease = Title::makeTitle( NS_MAIN, 'Cease' );
		$desist = Title::makeTitle( NS_MAIN, 'Desist' );
		$this->insertPage( $cease, 'to stop' );
		$this->insertPage( $desist, '#REDIRECT [[Cease]] __KEEPTITLE__' );

		$output = $this->viewPage( $cease, $desist );
		$this->assertStringContainsString( 'Desist', $output->getPageTitle() );
		$this->assertArrayNotHasKey( "wgRedirectedFrom", $output->getJSVars() );
	}

	public function testKeeptitleWithDisplaytitle(): void {
		$cease = Title::makeTitle( NS_MAIN, 'Cease' );
		$desist = Title::makeTitle( NS_MAIN, 'Desist' );
		$this->insertPage( $cease, 'to stop' );
		$this->insertPage( $desist, '#REDIRECT [[Cease]] __KEEPTITLE__ {{DISPLAYTITLE:\'\'Desist\'\'}}' );

		$output = $this->viewPage( $cease, $desist );
		$this->assertStringContainsString( '<i>Desist</i>', $output->getPageTitle() );
	}

	/* public function testKeeptitleRedirect(): void { */
	/* 	$this->insertPage( 'Cease', 'to stop' ); */
	/* } */
}
