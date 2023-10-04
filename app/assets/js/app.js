/**
 * Search form result.
 */
import React from 'react';
import { SearchForm } from "./components/search-form";
import { render } from 'react-dom';
import {Ticket} from "./components/ticket-page";

const form = document.getElementById( 'search-form' );
if ( form ) {
  render( <SearchForm />, form );
}

const ticketWrapper = document.getElementById( 'ticket' );
if ( ticketWrapper ) {
  render( <Ticket id={ ticketWrapper.dataset.ticketId } />, ticketWrapper );
}
