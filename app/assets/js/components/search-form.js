/**
 * Search Form
 */
import React from 'react';
import { Component } from 'react';
import { SearchBox } from "./search-box";
import { LoadingIndicator } from "./loading";
import {Ticket} from "./ticket-page";
import {fetchApi} from "./helper";

export class SearchForm extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      tickets: [],
      active: 0,
      loading: false,
    };
  }

  onSubmit( text ){
    if ( this.state.loading || ! text.length ) {
      // If loading, fix
      return;
    }
    this.setState( {
      loading: true,
      active: 0,
      tickets: [],
    }, () => {
      fetchApi( '/search?s=' + encodeURIComponent( text ) )
        .then( res => res.json() )
        .then( items => {
          console.log( items );
          this.setState( {
            tickets: items,
          } );
        } )
        .catch( res => {
          console.log( res );
        } )
        .finally( res => {
          this.setState( {
            loading: false,
          } );
        } );
    } );
  }

  render(){
    return (
      <div className='search'>
        <SearchBox onSubmit={ text => this.onSubmit( text ) } />

        <hr />

        <LoadingIndicator loading={ this.state.loading } />

        <p className='text-center text-muted'>{ this.state.tickets.length }件が見つかりました。</p>

        { this.state.tickets.length ? (
          <table className='table search-result'>
            <thead>
              <tr>
                <th>#</th>
                <th>名前</th>
                <th>種別</th>
                <th>メール</th>
                <th>アクション</th>
              </tr>
            </thead>
            <tbody>
              { this.state.tickets.map( ( ticket, index ) => {
                console.log(ticket);
                const url = `/qr/{ticket.id}`;
                return (
                  <tr ref={ ticket.id }>
                    <th><a href={url}>{ticket.attendee_id}</a></th>
                    <td>{ticket.last_name} {ticket.first_name}</td>
                    <td>{ticket.role}</td>
                    <td>{ticket.email}</td>
                    <td>
                      <button className='btn btn-primary' onClick={ e => this.setState( { active: ticket.id} ) }>表示</button>
                    </td>
                  </tr>
                );
              } ) }
            </tbody>
          </table>
        ) : (
          <div className='alert alert-danger text-center'>
            該当するチケットはありません。
          </div>
        ) }

        { this.state.active ? (
          <div className='backdrop'>
            <div className='backdrop-inner'>
              <button className='btn btn-link backdrop-close' onClick={ e => this.setState( { active: 0 } ) }>閉じる</button>
              <h3 className='text-center'>チケット詳細</h3>
              <div className='ticket-wrapper'>
                <Ticket id={ this.state.active } />
              </div>
            </div>
          </div>
        ) : null }

      </div>
    );
  }

}
