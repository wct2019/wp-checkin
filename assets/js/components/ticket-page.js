/**
 * Ticket component.
 */

import React from 'react';
import { Component } from 'react';

export class Ticket extends Component {



  render(){
    return <p>これは{this.props.id}のチケットです。</p>;
  }

}
