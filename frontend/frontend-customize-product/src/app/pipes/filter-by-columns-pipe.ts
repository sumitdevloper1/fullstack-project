import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'filterByColumns',
  standalone: true,
  pure: false // ✅ required for live filtering
})
export class FilterByColumnsPipe implements PipeTransform {

  
  transform(items: any[], filters: any): any[] {
    if (!items || !filters) return items;

    // collect only active filters
    const activeKeys = Object.keys(filters).filter(
      key => filters[key]?.toString().trim() !== ''
    );


    if (activeKeys.length === 0) return items;

    return items.filter(item =>
      activeKeys.every(key => {
        const term = filters[key].toString().toLowerCase().trim();

        switch (key) {

          case 'unique_code':
            return item.unique_code?.toLowerCase().includes(term);

          case 'created_at':
            return item.created_at?.toLowerCase().includes(term);

          case 'standard_code_id':
            return item.standard_code_id?.toString().includes(term);

          case 'standard_code':
            return item.standard_code?.code?.toLowerCase().includes(term);

          case 'mark':
            return item.printing_color_mark_text?.toLowerCase().includes(term);

          case 'print':
            return item.printing_color_print_text?.toLowerCase().includes(term);

          case 'engraving':
            return item.engraving?.toLowerCase().includes(term);

          case 'added':
            return item.add_accessories_text?.toLowerCase().includes(term);

          case 'removed':
            return item.remove_accessories_text?.toLowerCase().includes(term);

          case 'note':
            return item.specifications?.[0]?.note?.toLowerCase().includes(term);

          case 'capacity':
            return item.specifications?.[0]?.capacity?.toLowerCase().includes(term);

          case 'neck':
            return item.specifications?.[0]?.neck_size?.toLowerCase().includes(term);

          case 'item_name':
            return item.specifications?.[0]?.item_name?.toLowerCase().includes(term);

          case 'item_description':
            return item.specifications?.[0]?.item_description?.toLowerCase().includes(term);

          case 'remarks':
            return item.specifications?.[0]?.remarks?.toLowerCase().includes(term);

          case 'vendor_name':
            return item.specifications?.[0]?.vendor_name?.toLowerCase().includes(term);

          case 'pack_size':
            return item.specifications?.[0]?.pack_size?.toString().includes(term);

          case 'moq':
            return item.specifications?.[0]?.moq?.toString().includes(term);

          default:
            return true;
        }
      })
    );
  }
}
