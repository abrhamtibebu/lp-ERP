import { computed } from 'vue';

export const CURRENCIES = {
  USD: {
    code: 'USD',
    symbol: '$',
    name: 'US Dollar'
  },
  ETB: {
    code: 'ETB',
    symbol: 'Br',
    name: 'Ethiopian Birr'
  }
};

export function useCurrency(currency = 'USD') {
  const formatCurrency = (amount, currencyCode = currency) => {
    if (amount === null || amount === undefined) return '0.00';
    
    const amountNum = parseFloat(amount) || 0;
    const currencyInfo = CURRENCIES[currencyCode] || CURRENCIES.USD;
    
    // Format number with commas
    const formatted = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amountNum);
    
    // Add currency symbol
    if (currencyCode === 'USD') {
      return `${currencyInfo.symbol}${formatted}`;
    } else {
      return `${formatted} ${currencyInfo.symbol}`;
    }
  };

  const formatCurrencyCompact = (amount, currencyCode = currency) => {
    if (amount === null || amount === undefined) return '0';
    
    const amountNum = parseFloat(amount) || 0;
    const currencyInfo = CURRENCIES[currencyCode] || CURRENCIES.USD;
    
    const formatted = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amountNum);
    
    if (currencyCode === 'USD') {
      return `${currencyInfo.symbol}${formatted}`;
    } else {
      return `${formatted} ${currencyInfo.symbol}`;
    }
  };

  const getCurrencySymbol = (currencyCode = currency) => {
    const currencyInfo = CURRENCIES[currencyCode] || CURRENCIES.USD;
    return currencyInfo.symbol;
  };

  const getCurrencyLabel = (currencyCode = currency) => {
    const currencyInfo = CURRENCIES[currencyCode] || CURRENCIES.USD;
    return `${currencyInfo.code} (${currencyInfo.symbol})`;
  };

  return {
    formatCurrency,
    formatCurrencyCompact,
    getCurrencySymbol,
    getCurrencyLabel,
    CURRENCIES
  };
}

