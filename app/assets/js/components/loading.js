import React from 'react';

export class LoadingIndicator extends React.Component {

  render() {
    if ( ! this.props.loading ) {
      return null;
    } else {
      return (
        <div className="alert alert-light text-center" role="alert">
          <p><i className='fas fa-spinner text-dark fa-spin' style={ { 'font-size': '80px' } }></i></p>
          <p className='mb-0'>読み込み中……</p>
        </div>
      );
    }
  }

}
