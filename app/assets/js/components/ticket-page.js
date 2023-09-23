/**
 * Ticket component.
 */

import React from 'react';
import { Component } from 'react';
import classNames from 'classnames';
import { fetchApi } from "./helper";
import { LoadingIndicator } from "./loading";

export class Ticket extends Component {

  constructor( props ) {
    super( props );
    this.load = this.load.bind( this );
    this.state = {
      loading: true,
      ticket: null,
    };
  }

  load() {
    this.setState( {
      loading: true,
    }, () => {
      fetchApi( `/ticket/${this.props.id}/detail` )
        .then( res => res.json() )
        .then( data => {
          this.setState( {
            ticket: data,
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

  componentDidMount() {
    this.load();
  }

  handleCheckin() {
    this.setState( {
      loading: true,
    }, () => {
      fetchApi( `/ticket/${this.props.id}/detail`, {
        method: 'POST'
      } )
        .then( res => {
          if ( res.ok ) {
            return res.json();
          } else {
            return res.json().then( json => {
              throw Error( json.message );
            } );
          }
        } )
        .then( data => {
          this.setState( {
            ticket: data,
          } );
        } )
        .catch( res => {
          alert( res );
        } )
        .finally( res => {
          this.setState( {
            loading: false,
          } );
        } );
    } );
  }

  handleCancel() {
    if ( ! window.confirm( 'チェックインを取り消してよろしいですか？' ) ) {
      return;
    }
    this.setState( {
      loading: true,
    }, () => {
      fetchApi( `/ticket/${this.props.id}/detail`, {
        method: 'DELETE'
      } )
        .then( res => {
          if ( res.ok ) {
            return res.json();
          } else {
            return res.json().then( json => {
              throw Error( json.message );
            } );
          }
        } )
        .then( data => {
          this.setState( {
            ticket: data,
          } );
        } )
        .catch( res => {
          alert( res );
        } )
        .finally( res => {
          this.setState( {
            loading: false,
          } );
        } );
    } );
  }

  render(){
    const classes = classNames( 'ticket-wrapper', {
      loading: this.state.loading,
      empty: ! this.state.ticket
    } );
    const { ticket } = this.state;
    return (
      <div className={ classes }>
        { this.state.loading ? (
          <LoadingIndicator loading={ this.state.loading } />
        ) : ( this.state.ticket ? (
          <>
            <table className='table'>
              <caption> Ticket #{ ticket.id } </caption>
              <tbody>
                <tr>
                  <th>姓名</th>
                  <td>{ticket.last_name} {ticket.first_name}</td>
                </tr>
                <tr>
                  <th>種別</th>
                  <td>{ticket.role}</td>
                </tr>
                <tr>
                  <th>メール</th>
                  <td>{ticket.email}</td>
                </tr>
                <tr>
                  <th>チケット種別</th>
                  <td>
                    {ticket.ticket_type}
                    <small className={ 'Publish' !== ticket.status ? 'text-danger' : 'text-success' }>({ticket.status})</small>
                  </td>
                </tr>
                <tr>
                  <th>食事制限</th>
                  <td>{ ticket.meal_preference ? ticket.meal_preference : <span className='text-danger'>いいえ</span>}</td>
                </tr>
                <tr>
                  <th>チェックイン</th>
                  <td>{ ticket.checkedin ? (
                    <span>
                      <i className='fas fa-check text-success'></i>&nbsp;
                      { ticket.checkedin }
                    </span>
                  ) : '---' }</td>
                </tr>
              </tbody>
            </table>
            <div className='text-center'>
              { ( 'Publish' !== ticket.status ) ? (
                <div className='alert alert-warning'>これは取り消されたチケットです。</div>
              ) : ( ticket.checkedin ? (
                <button className='btn btn-link' onClick={ e => this.handleCancel() }>取り消し</button>
              ) : (
                <button className='btn btn-lg btn-outline-success' onClick={ e => this.handleCheckin() }>チェックイン</button>
              ) ) }
            </div>
            { ticket.checkedin ? (
              <div className='ticket-checked'>
                <hr />
                <h3 className='ticket-checked-title'>お渡しするもの</h3>
                <ol className='ticket-checked-list'>
                  { ticket.items.map( ( item, index ) => {
                    return <li>{item}</li>
                  } ) }
                </ol>
              </div>
            ) : null }
          </>
        ) : (
          <div className='alert alert-danger text-center'>#{this.props.id}は見つかりませんでした。</div>
        ) ) }
      </div>
    );
  }
}
