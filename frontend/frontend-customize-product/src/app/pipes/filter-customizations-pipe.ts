import { Pipe, PipeTransform } from '@angular/core';

/**
 * 🔹 Pipe 1: Filters customization list by unique_code
 */
@Pipe({
  name: 'filterCustomizations',
  standalone: true
})
export class FilterCustomizationsPipe implements PipeTransform {
  /**
   * Filters an array of customization objects based on a unique code search term.
   * @param items The array of customization objects to filter.
   * @param searchTerm The unique code string to search for (case-insensitive).
   * @returns The filtered array.
   */
  transform(items: any[] | null, searchTerm: string): any[] {
    if (!items || !searchTerm) {
      return items || [];
    }

    const term = searchTerm.toLowerCase().trim();

    return items.filter(item => {
      return item.unique_code 
        ? item.unique_code.toLowerCase().includes(term)
        : false;
    });
  }
}

/**
 * 🔹 Pipe 2: Generic filter pipe for accessories (search by name or any field)
 * Usage: items | filter:searchText:'fieldName'
 */
@Pipe({
  name: 'filter',
  standalone: true
})
export class FilterPipe implements PipeTransform {
  transform(items: any[], searchText: string, field: string): any[] {
    if (!items) return [];
    if (!searchText) return items;
    searchText = searchText.toLowerCase();
    return items.filter(it => it[field]?.toLowerCase().includes(searchText));
  }
}
