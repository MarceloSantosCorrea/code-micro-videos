import React from 'react';
import {Navbar} from "./components/Navbar";
import {Page} from "./components/Page";
import {Box} from "@material-ui/core";

function App() {
  return (
    <React.Fragment>
      <Navbar/>
      <Box paddingTop={'70px'}>
        <Page title="Dashboard">
          Dashboard
        </Page>
      </Box>
    </React.Fragment>
  )
}

export default App;
