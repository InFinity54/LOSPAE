using LOSPAÉ.ViewModels;
using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Windows.ApplicationModel.Store;
using Windows.Storage.Pickers;
using WinRT.Interop;

namespace LOSPAÉ.Views;

public sealed partial class SettingsPage : Page
{
    public SettingsViewModel ViewModel
    {
        get;
    }

    public SettingsPage()
    {
        ViewModel = App.GetService<SettingsViewModel>();
        InitializeComponent();
    }

    private async void CsvDataImport_Click(object sender, RoutedEventArgs e)
    {
        CsvDataImport.IsEnabled = false;
        CsvDataImportProgress.IsActive = true;

        var picker = new FileOpenPicker();
        picker.ViewMode = PickerViewMode.Thumbnail;
        picker.SuggestedStartLocation = PickerLocationId.ComputerFolder;
        picker.FileTypeFilter.Add(".csv");

        var WindowHandle = WindowNative.GetWindowHandle(App.MainWindow);
        InitializeWithWindow.Initialize(picker, WindowHandle);

        Windows.Storage.StorageFile file = await picker.PickSingleFileAsync();

        if (file != null)
        {
            ContentDialog dialog = new ContentDialog();

            // XamlRoot must be set in the case of a ContentDialog running in a Desktop app
            dialog.XamlRoot = this.XamlRoot;
            dialog.Style = Application.Current.Resources["DefaultContentDialogStyle"] as Style;
            dialog.Title = "Fichier sélectionné";
            dialog.PrimaryButtonText = "OK";
            dialog.DefaultButton = ContentDialogButton.Primary;
            dialog.Content = file.Name;

            await dialog.ShowAsync();
        }

        CsvDataImport.IsEnabled = true;
        CsvDataImportProgress.IsActive = false;
    }
}