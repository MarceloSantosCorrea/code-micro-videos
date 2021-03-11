import React, {useEffect, useState} from 'react'
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables"
import {httpVideo} from "../../util/http"
import {Chip} from "@material-ui/core"
import format from "date-fns/format"
import parseISO from "date-fns/parseISO"

const columnsDefinition: MUIDataTableColumn[] = [
  {
    name: 'name',
    label: 'Nome',
  },
  {
    name: 'is_active',
    label: 'Ativo?',
    options: {
      customBodyRender(value, tableMeta, updateValue) {

        return value ? <Chip label="Sim" color="primary"/> : <Chip label="Não"/>
      }
    }
  },
  {
    name: 'created_at',
    label: 'Criado em',
    options: {
      customBodyRender(value) {
        return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
      }
    }
  }
]

type Props = {};
const Table = (props: Props) => {

  const [data, setData] = useState([]);

  useEffect(() => {
    httpVideo.get('categories').then(response => setData(response.data.data))
  }, [])

  return (
    <div>
      <MUIDataTable
        title={'Listagem de Categorias'}
        columns={columnsDefinition}
        data={data}/>
    </div>
  );
};

export default Table;
