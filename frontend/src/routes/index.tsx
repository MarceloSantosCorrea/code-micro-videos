import {RouteProps} from 'react-router-dom'
import Dashboard from "../pages/Dashboard"
import CategoryList from "../pages/category/PageList"
import GenreList from "../pages/genre/PageList"
import CastMemberList from "../pages/cast-member/PageList"

export interface MyRouteProps extends RouteProps {
  name: string,
  label: string
}

const routes: MyRouteProps[] = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    path: '/',
    component: Dashboard,
    exact: true
  },
  {
    name: 'categories.list',
    label: 'Categorias',
    path: '/categories',
    component: CategoryList,
    exact: true
  },
  {
    name: 'categories.create',
    label: 'Nova Categoria',
    path: '/categories/create',
    component: CategoryList,
    exact: true
  },
  {
    name: 'categories.edit',
    label: 'Editar Categoria',
    path: '/categories/:id/edit',
    component: CategoryList,
    exact: true
  },
  {
    name: 'cast_members.list',
    label: 'Membros do Elenco',
    path: '/cast-menber',
    component: CastMemberList,
    exact: true
  },
  {
    name: 'cast_members.create',
    label: 'Novo Membro do Elenco',
    path: '/cast-menber/create',
    component: CastMemberList,
    exact: true
  },
  {
    name: 'cast_members.edit',
    label: 'Editar Membro do Elenco',
    path: '/cast-menber/:id/edit',
    component: CastMemberList,
    exact: true
  },
  {
    name: 'genres.list',
    label: 'Gêneros',
    path: '/genres',
    component: GenreList,
    exact: true
  },
  {
    name: 'genres.create',
    label: 'Novo Gênero',
    path: '/genres/create',
    component: GenreList,
    exact: true
  },
  {
    name: 'genres.edit',
    label: 'Editar Gênero',
    path: '/genres/:id/edit',
    component: GenreList,
    exact: true
  }
];

export default routes;
