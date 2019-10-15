/**
 * Search form result.
 */
import React from 'react';
import { SearchForm } from "./components/search-form";
import { render } from 'react-dom';

const form = document.getElementById( 'search-form' );
if ( form ) {
  render( <SearchForm />, form );
}
