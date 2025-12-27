import { Routes } from '@angular/router';
import { StandardCodeComponent } from './standard-code/standard-code.component';
import { CustomizeProduct } from './customize-product/customize-product';
import { CustomizationList } from './customization-list/customization-list';

export const routes: Routes = [
  { path: '', redirectTo: 'standard-code', pathMatch: 'full' },
  { path: 'standard-code', component: StandardCodeComponent },
  { path: 'customize-product', component: CustomizeProduct },
  { path: 'customization-list', component: CustomizationList },
  { path: 'customize-product/:id', component: CustomizeProduct },
];
